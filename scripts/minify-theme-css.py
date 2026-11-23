"""Minify theme-custom.css into theme-custom.min.css (web/app/themes/graceart-blokova/assets/css).

Run after editing theme-custom.css:  python scripts/minify-theme-css.py
Strings (including data: URIs) are kept intact; comments and redundant whitespace are removed.
"""
import re
from pathlib import Path

base = Path(__file__).resolve().parent.parent / 'web/app/themes/graceart-blokova/assets/css'
src = (base / 'theme-custom.css').read_text(encoding='utf-8')

# Protect strings, drop comments.
token = re.compile(r'''("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')|(/\*.*?\*/)''', re.S)
protected = []


def stash(match):
    if match.group(2):
        return ' '
    protected.append(match.group(1))
    return '\x00%d\x00' % (len(protected) - 1)


work = token.sub(stash, src)
work = re.sub(r'\s+', ' ', work)
work = re.sub(r'\s*([{};,])\s*', r'\1', work)
work = re.sub(r':\s+', ':', work).replace(';}', '}').strip()
work = re.sub(r'\x00(\d+)\x00', lambda m: protected[int(m.group(1))], work)

(base / 'theme-custom.min.css').write_text(work + '\n', encoding='utf-8', newline='\n')
print(len(src), '->', len(work))
