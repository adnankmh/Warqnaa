#!/usr/bin/env python3
"""Static release validation for Warqnaa V0.3 when Flutter/Composer are unavailable."""
from __future__ import annotations

import json
import re
import sys
import xml.etree.ElementTree as ET
from pathlib import Path

import yaml

ROOT = Path(__file__).resolve().parents[1]
SKIP_PARTS = {'.git', '.dart_tool', 'build', 'vendor', 'node_modules', 'storage', '.validation_v03'}
SOURCE_EXTENSIONS = {'.dart', '.php', '.py', '.js', '.ts', '.css', '.html', '.xml', '.json', '.yml', '.yaml'}


def files_with(*suffixes: str):
    wanted = set(suffixes)
    for path in ROOT.rglob('*'):
        if not path.is_file() or any(part in SKIP_PARTS for part in path.parts):
            continue
        if path.suffix.lower() in wanted:
            yield path


def rel(path: Path) -> str:
    return path.relative_to(ROOT).as_posix()


def fail(errors: list[str], message: str) -> None:
    errors.append(message)


def strip_dart_literals_and_comments(text: str) -> str:
    """Replace Dart comments/string contents with spaces while preserving newlines."""
    out = list(text)
    i = 0
    n = len(text)
    while i < n:
        ch = text[i]
        nxt = text[i + 1] if i + 1 < n else ''
        if ch == '/' and nxt == '/':
            j = i
            while j < n and text[j] != '\n':
                out[j] = ' '
                j += 1
            i = j
            continue
        if ch == '/' and nxt == '*':
            j = i
            depth = 1
            out[j] = out[j + 1] = ' '
            j += 2
            while j < n and depth:
                if j + 1 < n and text[j:j+2] == '/*':
                    out[j] = out[j + 1] = ' '
                    depth += 1
                    j += 2
                elif j + 1 < n and text[j:j+2] == '*/':
                    out[j] = out[j + 1] = ' '
                    depth -= 1
                    j += 2
                else:
                    if text[j] != '\n':
                        out[j] = ' '
                    j += 1
            i = j
            continue

        raw = False
        quote_pos = i
        if ch in ('r', 'R') and i + 1 < n and text[i + 1] in ("'", '"'):
            raw = True
            quote_pos = i + 1
        if text[quote_pos] in ("'", '"'):
            quote = text[quote_pos]
            triple = text[quote_pos:quote_pos+3] == quote * 3
            start = i
            j = quote_pos + (3 if triple else 1)
            while j < n:
                if triple:
                    if text[j:j+3] == quote * 3:
                        j += 3
                        break
                    if text[j] != '\n':
                        out[j] = ' '
                    j += 1
                    continue
                if text[j] == '\n':
                    break
                if not raw and text[j] == '\\':
                    out[j] = ' '
                    if j + 1 < n and text[j + 1] != '\n':
                        out[j + 1] = ' '
                    j += 2
                    continue
                if text[j] == quote:
                    j += 1
                    break
                out[j] = ' '
                j += 1
            for k in range(start, min(j, n)):
                if text[k] != '\n':
                    out[k] = ' '
            i = j
            continue
        i += 1
    return ''.join(out)


def validate_dart(path: Path, errors: list[str]) -> None:
    text = path.read_text(encoding='utf-8')
    clean = strip_dart_literals_and_comments(text)
    stack: list[tuple[str, int]] = []
    pairs = {')': '(', ']': '[', '}': '{'}
    openers = set(pairs.values())
    line = 1
    for ch in clean:
        if ch == '\n':
            line += 1
            continue
        if ch in openers:
            stack.append((ch, line))
        elif ch in pairs:
            if not stack or stack[-1][0] != pairs[ch]:
                fail(errors, f'{rel(path)}:{line}: unmatched {ch}')
                return
            stack.pop()
    if stack:
        ch, at = stack[-1]
        fail(errors, f'{rel(path)}:{at}: unclosed {ch}')

    for match in re.finditer(r"^\s*(?:part|import|export)\s+['\"]([^'\"]+)['\"]", text, re.M):
        target = match.group(1)
        if target.startswith(('dart:', 'package:', 'http:', 'https:')):
            continue
        target_path = (path.parent / target).resolve()
        try:
            target_path.relative_to(ROOT.resolve())
        except ValueError:
            fail(errors, f'{rel(path)}: local Dart reference escapes project: {target}')
            continue
        if not target_path.is_file():
            fail(errors, f'{rel(path)}: missing local Dart reference {target}')


def main() -> int:
    errors: list[str] = []
    counts = {'json': 0, 'yaml': 0, 'xml': 0, 'dart': 0, 'source': 0}

    for path in files_with('.json'):
        counts['json'] += 1
        try:
            json.loads(path.read_text(encoding='utf-8'))
        except Exception as exc:
            fail(errors, f'{rel(path)}: invalid JSON: {exc}')

    for path in files_with('.yml', '.yaml'):
        counts['yaml'] += 1
        try:
            yaml.safe_load(path.read_text(encoding='utf-8'))
        except Exception as exc:
            fail(errors, f'{rel(path)}: invalid YAML: {exc}')

    for path in files_with('.xml'):
        counts['xml'] += 1
        try:
            ET.parse(path)
        except Exception as exc:
            fail(errors, f'{rel(path)}: invalid XML: {exc}')

    for path in files_with('.dart'):
        counts['dart'] += 1
        try:
            validate_dart(path, errors)
        except UnicodeDecodeError as exc:
            fail(errors, f'{rel(path)}: invalid UTF-8: {exc}')

    marker = re.compile(r'^(<<<<<<< |=======\s*$|>>>>>>> )', re.M)
    for path in ROOT.rglob('*'):
        if not path.is_file() or any(part in SKIP_PARTS for part in path.parts):
            continue
        if path.suffix.lower() not in SOURCE_EXTENSIONS:
            continue
        counts['source'] += 1
        try:
            text = path.read_text(encoding='utf-8')
        except UnicodeDecodeError:
            continue
        if marker.search(text):
            fail(errors, f'{rel(path)}: unresolved Git conflict marker')

    if errors:
        print('[FAIL] Static validation found issues:')
        for item in errors:
            print(' - ' + item)
        return 1

    print('[PASS] Static release validation')
    print('JSON={json} YAML={yaml} XML={xml} DART={dart} SOURCE_SCAN={source}'.format(**counts))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
