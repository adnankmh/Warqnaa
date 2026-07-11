<?php

namespace App\Services\Notifications;

use App\Models\PushDevice;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class FirebasePushService
{
    private const OAUTH_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const OAUTH_AUDIENCE = 'https://oauth2.googleapis.com/token';

    /**
     * Send a notification to every registered device belonging to one user.
     * Returns the number of devices accepted by FCM.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        try {
            $devices = $user->relationLoaded('pushDevices')
                ? $user->pushDevices
                : $user->pushDevices()->get();

            $sent = 0;
            foreach ($devices as $device) {
                if ($device instanceof PushDevice && $this->sendToDevice($device, $title, $body, $data)) {
                    $sent++;
                }
            }

            return $sent;
        } catch (Throwable $error) {
            Log::warning('Warqna push dispatch skipped.', [
                'user_id' => $user->id,
                'error' => $error->getMessage(),
            ]);

            return 0;
        }
    }

    public function isConfigured(): bool
    {
        if (! (bool) config('push.enabled', true)) {
            return false;
        }

        $credentials = $this->credentials();

        return is_array($credentials)
            && filled($this->projectId($credentials))
            && filled($credentials['client_email'] ?? null)
            && filled($credentials['private_key'] ?? null);
    }

    private function sendToDevice(PushDevice $device, string $title, string $body, array $data): bool
    {
        try {
            $credentials = $this->credentials();
            if (! is_array($credentials)) {
                return false;
            }

            $accessToken = $this->accessToken($credentials);
            $projectId = $this->projectId($credentials);
            $deviceToken = (string) $device->token;
            if ($accessToken === '' || $projectId === '' || $deviceToken === '') {
                return false;
            }

            $stringData = [];
            foreach ($data as $key => $value) {
                if ($value === null) {
                    continue;
                }
                $stringData[(string) $key] = is_scalar($value)
                    ? (string) $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $stringData['title'] = $title;
            $stringData['body'] = $body;

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->asJson()
                ->timeout(max(3, (int) config('push.timeout_seconds', 12)))
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $deviceToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $stringData,
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => (string) config('push.default_channel_id', 'warqna_messages'),
                                'sound' => 'default',
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'content-available' => 1,
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $device->forceFill(['last_seen_at' => now()])->save();
                return true;
            }

            $payload = $response->json();
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
            if ($response->status() === 404
                || Str::contains($encoded, ['UNREGISTERED', 'registration-token-not-registered'])) {
                $device->delete();
            }

            Log::notice('FCM rejected a Warqna notification.', [
                'device_id' => $device->id,
                'status' => $response->status(),
                'response' => mb_substr($encoded, 0, 800),
            ]);

            return false;
        } catch (Throwable $error) {
            Log::warning('FCM device send failed safely.', [
                'device_id' => $device->id,
                'error' => $error->getMessage(),
            ]);

            return false;
        }
    }

    private function accessToken(array $credentials): string
    {
        $projectId = $this->projectId($credentials);
        $cacheKey = 'warqna:fcm:access-token:'.hash('sha256', $projectId.'|'.($credentials['client_email'] ?? ''));
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $now = time();
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64Url(json_encode([
            'iss' => (string) $credentials['client_email'],
            'scope' => self::OAUTH_SCOPE,
            'aud' => self::OAUTH_AUDIENCE,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header.'.'.$claims;

        $signature = '';
        $signed = openssl_sign($unsigned, $signature, (string) $credentials['private_key'], OPENSSL_ALGO_SHA256);
        if (! $signed) {
            throw new \RuntimeException('Unable to sign Firebase service-account JWT.');
        }

        $assertion = $unsigned.'.'.$this->base64Url($signature);
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(max(3, (int) config('push.timeout_seconds', 12)))
            ->post((string) ($credentials['token_uri'] ?? self::OAUTH_AUDIENCE), [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ])
            ->throw();

        $token = (string) $response->json('access_token', '');
        if ($token === '') {
            throw new \RuntimeException('Firebase OAuth response did not contain an access token.');
        }

        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return $token;
    }

    private function credentials(): ?array
    {
        static $resolved = false;
        static $credentials = null;
        if ($resolved) {
            return $credentials;
        }
        $resolved = true;

        $raw = trim((string) config('push.service_account_b64', ''));
        if ($raw !== '') {
            $decoded = base64_decode($raw, true);
            if (is_string($decoded)) {
                $credentials = json_decode($decoded, true);
            }
        }

        if (! is_array($credentials)) {
            $raw = trim((string) config('push.service_account_json', ''));
            if ($raw !== '') {
                $credentials = json_decode($raw, true);
            }
        }

        if (! is_array($credentials)) {
            $path = trim((string) config('push.service_account_path', ''));
            if ($path !== '') {
                $resolvedPath = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);
                if (is_file($resolvedPath) && is_readable($resolvedPath)) {
                    $credentials = json_decode((string) file_get_contents($resolvedPath), true);
                }
            }
        }

        return is_array($credentials) ? $credentials : null;
    }

    private function projectId(array $credentials): string
    {
        return trim((string) (config('push.project_id') ?: ($credentials['project_id'] ?? '')));
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
