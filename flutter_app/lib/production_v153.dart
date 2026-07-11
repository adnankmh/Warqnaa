part of 'main.dart';

String v153Text(String locale, String key) {
  const values = <String, Map<String, String>>{
    'ar': {
      'productionCenter': 'مركز الأمان والإطلاق',
      'productionCenterHint': 'حالة الخادم، الخصوصية، الجلسات، تصدير البيانات والبلاغات',
      'server': 'الخادم والإصدار', 'privacy': 'الخصوصية والبيانات', 'sessions': 'الجلسات والأجهزة',
      'export': 'تصدير بياناتي', 'report': 'إرسال بلاغ', 'legal': 'الصفحات القانونية',
      'voice': 'جاهزية الغرف الصوتية', 'online': 'متصل', 'offline': 'وضع محلي', 'copy': 'نسخ',
      'deleteRequest': 'إلغاء الحساب', 'cancelDelete': 'التراجع عن إلغاء الحساب', 'verifyEmail':'تأكيد البريد الإلكتروني', 'verifyEmailHint':'إرسال رابط تأكيد إلى بريد الحساب',
    },
    'en': {
      'productionCenter': 'Safety & Launch Center',
      'productionCenterHint': 'Server status, privacy, sessions, export and reports',
      'server': 'Server & release', 'privacy': 'Privacy & data', 'sessions': 'Sessions & devices',
      'export': 'Export my data', 'report': 'Submit report', 'legal': 'Legal pages',
      'voice': 'Voice-room readiness', 'online': 'Online', 'offline': 'Local mode', 'copy': 'Copy',
      'deleteRequest': 'Cancel account', 'cancelDelete': 'Reactivate account', 'verifyEmail':'Verify email address', 'verifyEmailHint':'Send a verification link to the account email',
    },
    'de': {'productionCenter':'Sicherheits- & Startcenter','productionCenterHint':'Serverstatus, Datenschutz, Sitzungen, Export und Meldungen','server':'Server & Version','privacy':'Datenschutz & Daten','sessions':'Sitzungen & Geräte','export':'Meine Daten exportieren','report':'Meldung senden','legal':'Rechtliche Seiten','voice':'Sprachraum-Bereitschaft','online':'Online','offline':'Lokaler Modus','copy':'Kopieren','deleteRequest':'Konto deaktivieren','cancelDelete':'Konto reaktivieren','verifyEmail':'E-Mail bestätigen','verifyEmailHint':'Bestätigungslink an die Konto-E-Mail senden'},
    'tr': {'productionCenter':'Güvenlik ve Yayın Merkezi','productionCenterHint':'Sunucu durumu, gizlilik, oturumlar, dışa aktarma ve raporlar','server':'Sunucu ve sürüm','privacy':'Gizlilik ve veriler','sessions':'Oturumlar ve cihazlar','export':'Verilerimi dışa aktar','report':'Rapor gönder','legal':'Yasal sayfalar','voice':'Sesli oda hazırlığı','online':'Çevrimiçi','offline':'Yerel mod','copy':'Kopyala','deleteRequest':'Hesabı iptal et','cancelDelete':'Hesabı yeniden etkinleştir','verifyEmail':'E-postayı doğrula','verifyEmailHint':'Hesap e-postasına doğrulama bağlantısı gönder'},
    'fr': {'productionCenter':'Centre sécurité et lancement','productionCenterHint':'État serveur, confidentialité, sessions, export et signalements','server':'Serveur et version','privacy':'Confidentialité et données','sessions':'Sessions et appareils','export':'Exporter mes données','report':'Envoyer un signalement','legal':'Pages légales','voice':'État des salons vocaux','online':'En ligne','offline':'Mode local','copy':'Copier','deleteRequest':'Annuler le compte','cancelDelete':'Réactiver le compte','verifyEmail':'Vérifier l’adresse e-mail','verifyEmailHint':'Envoyer un lien de vérification à l’e-mail du compte'},
    'es': {'productionCenter':'Centro de seguridad y lanzamiento','productionCenterHint':'Estado del servidor, privacidad, sesiones, exportación y reportes','server':'Servidor y versión','privacy':'Privacidad y datos','sessions':'Sesiones y dispositivos','export':'Exportar mis datos','report':'Enviar reporte','legal':'Páginas legales','voice':'Estado de salas de voz','online':'En línea','offline':'Modo local','copy':'Copiar','deleteRequest':'Cancelar cuenta','cancelDelete':'Reactivar cuenta','verifyEmail':'Verificar correo','verifyEmailHint':'Enviar un enlace de verificación al correo de la cuenta'},
  };
  return values[locale]?[key] ?? values['en']![key] ?? key;
}

Future<void> openProductionCenterV153(BuildContext context, AppController controller) async {
  await Navigator.push(context, MaterialPageRoute(builder: (_) => ProductionCenterV153(controller: controller)));
}

class ProductionCenterV153 extends StatefulWidget {
  final AppController controller;
  const ProductionCenterV153({super.key, required this.controller});
  @override
  State<ProductionCenterV153> createState() => _ProductionCenterV153State();
}

class _ProductionCenterV153State extends State<ProductionCenterV153> {
  bool loading = true;
  Map<String, dynamic>? config;
  String? error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { loading = true; error = null; });
    try {
      final response = await widget.controller.api.platformConfig();
      config = response['config'] is Map ? Map<String, dynamic>.from(response['config'] as Map) : <String, dynamic>{};
    } catch (e) {
      error = e.toString();
    }
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final c = widget.controller;
    String t(String key) => v153Text(c.localeCode, key);
    final legal = config?['legal'] is Map ? Map<String, dynamic>.from(config!['legal'] as Map) : <String, dynamic>{};
    final voice = config?['voice'] is Map ? Map<String, dynamic>.from(config!['voice'] as Map) : <String, dynamic>{};
    return Scaffold(
      appBar: AppBar(title: Text(t('productionCenter')), actions: [IconButton(onPressed: _load, icon: const Icon(Icons.refresh_rounded))]),
      body: SafeArea(
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(14),
                children: [
                  PremiumPanel(child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                      Row(children: [
                        Icon(c.serverConnected ? Icons.cloud_done_rounded : Icons.cloud_off_rounded, color: c.serverConnected ? Colors.greenAccent : Colors.amber),
                        const SizedBox(width: 10),
                        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text(t('server'), style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                          Text('${c.serverConnected ? t('online') : t('offline')} • v$warqnaAppVersion ($warqnaAppBuild)', style: const TextStyle(color: Colors.white60)),
                        ])),
                        Chip(label: Text(warqnaProductionMode ? 'PRODUCTION' : 'PREVIEW', style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900))),
                      ]),
                      if (error != null) ...[const SizedBox(height: 10), Text(error!, style: const TextStyle(color: Colors.orangeAccent, fontSize: 11))],
                      if (config != null) ...[
                        const SizedBox(height: 10),
                        Text('API: ${c.api.baseUrl}', textDirection: TextDirection.ltr, style: const TextStyle(fontSize: 10, color: Colors.white54)),
                        Text('Server: ${config!['version'] ?? '-'} • ${config!['environment'] ?? '-'}', style: const TextStyle(fontSize: 10, color: Colors.white54)),
                      ],
                    ]),
                  )),
                  const SizedBox(height: 10),
                  PremiumPanel(child: Column(children: [
                    ListTile(leading: const Icon(Icons.mic_rounded, color: Colors.lightBlueAccent), title: Text(t('voice')), subtitle: Text(voice.isEmpty ? 'WebRTC • STUN/TURN status requires server' : 'Enabled: ${voice['enabled']} • TURN: ${voice['turn_required']}')),
                    ListTile(leading: const Icon(Icons.download_rounded), title: Text(t('export')), subtitle: const Text('JSON export of account, profile, inventory, wallet and recent messages'), onTap: _exportData),
                    ListTile(leading: const Icon(Icons.devices_rounded), title: Text(t('sessions')), subtitle: const Text('Review and close active API sessions'), onTap: _showSessions),
                    ListTile(leading: const Icon(Icons.mark_email_read_rounded, color: Colors.lightGreenAccent), title: Text(t('verifyEmail')), subtitle: Text(t('verifyEmailHint')), onTap: _sendVerification),
                    ListTile(leading: const Icon(Icons.report_gmailerrorred_rounded, color: Colors.orangeAccent), title: Text(t('report')), subtitle: const Text('Report abuse, cheating, spam or inappropriate content'), onTap: _showReportDialog),
                  ])),
                  const SizedBox(height: 10),
                  PremiumPanel(child: Column(children: [
                    ListTile(leading: const Icon(Icons.policy_rounded), title: Text(t('legal')), subtitle: const Text('Privacy • Terms • Community • Competitions • Support')),
                    ...legal.entries.map((entry) => ListTile(
                      dense: true,
                      leading: const Icon(Icons.open_in_new_rounded, size: 18),
                      title: Text(entry.key.replaceAll('_', ' ')),
                      subtitle: Text(entry.value.toString(), maxLines: 1, overflow: TextOverflow.ellipsis, textDirection: TextDirection.ltr),
                      trailing: IconButton(icon: const Icon(Icons.copy_rounded), tooltip: t('copy'), onPressed: () async { final messenger = ScaffoldMessenger.of(context); await Clipboard.setData(ClipboardData(text: entry.value.toString())); if (mounted) messenger.showSnackBar(const SnackBar(content: Text('Copied'))); }),
                    )),
                  ])),
                  const SizedBox(height: 10),
                  if (!c.isAdmin) PremiumPanel(child: Column(children: [
                    ListTile(leading: const Icon(Icons.person_off_rounded, color: Colors.redAccent), title: Text(t('deleteRequest')), subtitle: const Text('Password protected • permanent deletion after 30 days without reopening'), onTap: _requestDeletion),
                    ListTile(leading: const Icon(Icons.restore_from_trash_rounded), title: Text(t('cancelDelete')), subtitle: const Text('سجّل الدخول إلى الحساب خلال 30 يوماً لإعادة تفعيله تلقائياً.')),
                  ])),
                ],
              ),
      ),
    );
  }

  Future<void> _exportData() async {
    if (!widget.controller.serverConnected) { showToast(context, 'يتطلب تصدير البيانات اتصالاً بالخادم.'); return; }
    try {
      final data = await widget.controller.api.exportAccount();
      final text = const JsonEncoder.withIndent('  ').convert(data['export']);
      await Clipboard.setData(ClipboardData(text: text));
      if (mounted) showToast(context, 'تم نسخ تصدير البيانات بصيغة JSON.');
    } catch (e) { if (mounted) showToast(context, e.toString()); }
  }

  Future<void> _sendVerification() async {
    if (!widget.controller.serverConnected) { showToast(context, 'تأكيد البريد متاح بعد الاتصال بالخادم.'); return; }
    try {
      final result = await widget.controller.api.sendEmailVerification();
      if (mounted) showToast(context, result['message']?.toString() ?? 'تم إرسال رابط التأكيد.');
    } catch (e) { if (mounted) showToast(context, e.toString()); }
  }

  Future<void> _showSessions() async {
    if (!widget.controller.serverConnected) { showToast(context, 'إدارة الجلسات متاحة بعد الاتصال بالخادم.'); return; }
    try {
      final data = await widget.controller.api.sessions();
      final sessions = data['sessions'] is List ? List<Map<String, dynamic>>.from((data['sessions'] as List).map((e) => Map<String, dynamic>.from(e as Map))) : <Map<String, dynamic>>[];
      if (!mounted) return;
      await showDialog<void>(context: context, builder: (dialogContext) => AlertDialog(
        title: const Text('الجلسات النشطة'),
        content: SizedBox(width: 520, child: ListView(shrinkWrap: true, children: sessions.map((session) => ListTile(
          leading: Icon(session['current'] == true ? Icons.phone_android_rounded : Icons.devices_other_rounded),
          title: Text('${session['name'] ?? 'mobile'} ${session['current'] == true ? '• الحالية' : ''}'),
          subtitle: Text('${session['created_at'] ?? ''}\n${session['last_used_at'] ?? ''}', style: const TextStyle(fontSize: 10)),
          trailing: IconButton(icon: const Icon(Icons.logout_rounded), onPressed: () async { await widget.controller.api.revokeSession(int.parse(session['id'].toString())); if (dialogContext.mounted) Navigator.pop(dialogContext); }),
        )).toList())),
        actions: [TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('إغلاق'))],
      ));
    } catch (e) { if (mounted) showToast(context, e.toString()); }
  }

  Future<void> _showReportDialog() async {
    if (!widget.controller.serverConnected) { showToast(context, 'إرسال البلاغات يتطلب اتصالاً بالخادم.'); return; }
    String category = 'other';
    final details = TextEditingController();
    final submitted = await showDialog<bool>(context: context, builder: (dialogContext) => StatefulBuilder(builder: (context, setLocalState) => AlertDialog(
      title: const Text('إرسال بلاغ إلى فريق السلامة'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        DropdownButtonFormField<String>(initialValue: category, decoration: const InputDecoration(labelText: 'نوع البلاغ'), items: const [
          DropdownMenuItem(value:'harassment',child:Text('إساءة أو تحرش')), DropdownMenuItem(value:'cheating',child:Text('غش')), DropdownMenuItem(value:'spam',child:Text('إزعاج')), DropdownMenuItem(value:'impersonation',child:Text('انتحال هوية')), DropdownMenuItem(value:'inappropriate_content',child:Text('محتوى غير مناسب')), DropdownMenuItem(value:'other',child:Text('أخرى')),
        ], onChanged: (value) => setLocalState(() => category = value ?? 'other')),
        const SizedBox(height: 10),
        TextField(controller: details, maxLines: 4, decoration: const InputDecoration(labelText: 'التفاصيل', hintText: 'اكتب ما حدث بوضوح...')),
      ]),
      actions: [TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('إلغاء')), FilledButton(onPressed: () => Navigator.pop(dialogContext, true), child: const Text('إرسال'))],
    ))) ?? false;
    if (!submitted) { details.dispose(); return; }
    try { await widget.controller.api.submitReport(category: category, details: details.text); if (mounted) showToast(context, 'تم إرسال البلاغ.'); } catch (e) { if (mounted) showToast(context, e.toString()); }
    details.dispose();
  }

  Future<void> _requestDeletion() async {
    if (!widget.controller.serverConnected) {
      showToast(context, 'إلغاء الحساب يتطلب اتصالاً بالخادم.');
      return;
    }
    final password = TextEditingController();
    final reason = TextEditingController();
    final confirmed = await showDialog<bool>(
          context: context,
          builder: (dialogContext) => AlertDialog(
            title: const Text('إلغاء الحساب'),
            content: Column(mainAxisSize: MainAxisSize.min, children: [
              const Text(
                'هل أنت متأكد أنك سوف تلغي الحساب؟ سيتم تسجيل خروجك الآن، وسيُحذف الحساب نهائياً إذا لم تفتحه وتسجل الدخول خلال 30 يوماً.',
                style: TextStyle(height: 1.55),
              ),
              const SizedBox(height: 10),
              TextField(controller: password, obscureText: true, decoration: const InputDecoration(labelText: 'كلمة المرور')),
              const SizedBox(height: 8),
              TextField(controller: reason, decoration: const InputDecoration(labelText: 'السبب (اختياري)')),
            ]),
            actions: [
              TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('تراجع')),
              FilledButton(
                onPressed: () => Navigator.pop(dialogContext, true),
                style: FilledButton.styleFrom(backgroundColor: Colors.redAccent),
                child: const Text('نعم، إلغاء الحساب'),
              ),
            ],
          ),
        ) ??
        false;
    if (!confirmed) {
      password.dispose();
      reason.dispose();
      return;
    }

    final error = await widget.controller.cancelAccount(password.text, reason: reason.text);
    password.dispose();
    reason.dispose();
    if (!mounted) return;
    showToast(context, error ?? 'تم إلغاء الحساب. يمكنك استعادته بتسجيل الدخول خلال 30 يوماً.');
    if (error == null) Navigator.pop(context);
  }

}
