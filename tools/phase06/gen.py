# Phase 06 backlog generator and validator (documentation tooling, not application code).
# Usage: python -I tools/phase06/gen.py <project_root>
# Reads tools/phase06/backlog.txt and templates.md, writes docs/phase-06/01..06, 08..10, prints the validation results.
import os, re, sys, collections

ROOT = sys.argv[1]
HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(ROOT, 'docs', 'phase-06')
rd = lambda *p: open(os.path.join(*p), encoding='utf-8').read()

# ---------- parse source ----------
epics, sprints, risks, questions, stories = {}, {}, {}, {}, collections.OrderedDict()
cur = None
for raw in rd(HERE, 'backlog.txt').splitlines():
    line = raw.rstrip()
    if not line or line.startswith('//'):
        continue
    if line[0] in '#!^?@':
        cur = None
        parts = [p.strip() for p in line[1:].split(' | ')]
        if line[0] == '#': epics[parts[0]] = dict(id=parts[0], name=parts[1], goal=parts[2], modules=parts[3])
        elif line[0] == '!': sprints[parts[0]] = dict(id=parts[0], name=parts[1], goal=parts[2], deliverables=parts[3], risks=parts[4], gate=parts[5])
        elif line[0] == '^': risks[parts[0]] = dict(id=parts[0], desc=parts[1], prob=parts[2], impact=parts[3], mitigation=parts[4], epics=parts[5], owner=parts[6], stories=parts[7])
        elif line[0] == '?': questions[parts[0]] = dict(id=parts[0], q=parts[1], why=parts[2], impact=parts[3], owner=parts[4], status=parts[5])
        else:
            cur = dict(id=parts[0], epic=parts[1], title=parts[2], ac=[], be=[], bt=[], fe=[], ft=[], inf=[], qs=[], deps=[], api=[], tables=[], note='', prio='')
            stories[parts[0]] = cur
        continue
    k, _, v = line.partition(':')
    k, v = k.strip(), v.strip()
    if cur is None:
        raise SystemExit('orphan line: ' + line)
    if k == 'ac':
        kind, _, text = v.partition(' | ')
        cur['ac'].append((kind.strip(), text.strip()))
    elif k in ('be', 'bt', 'fe', 'ft', 'inf'):
        cur[k].append(v)
    elif k in ('qs', 'deps', 'reqs'):
        cur[k] = [x.strip() for x in v.split(',') if x.strip()]
    elif k in ('api', 'tables'):
        cur[k] = [x.strip() for x in re.split(r'[;,]', v) if x.strip()]
    else:
        cur[k] = v

# ---------- reference data ----------
srs = rd(ROOT, 'docs', 'requirements', 'SRS.md')
REQ = collections.OrderedDict()
for rid, pri, cls in re.findall(r'^#### (\S+)\n\*\*Priority:\*\* ([^·]+?) · \*\*Classification:\*\* ([A-Z ]+?) ·', srs, re.M):
    REQ[rid] = dict(pri=pri.strip(), cls=cls.strip())
acs = set(re.findall(r'AC-[A-Z]+-[A-Z0-9]+-\d\d-\d', rd(ROOT, 'docs', 'requirements', 'acceptance-criteria.md')))
oq_text = rd(ROOT, 'docs', 'requirements', 'open-questions.md')
known_q = set(re.findall(r'\bQ-\d\d\b', oq_text))
brs = set(re.findall(r'\bBR-\d\d-\d\d\b', rd(ROOT, 'docs', 'requirements', 'business-rules.md')))
overview = rd(ROOT, 'docs', 'architecture', 'system-overview.md')
known_c = set(re.findall(r'^\| (C-\d\d) \|', overview, re.M))
api_doc = rd(ROOT, 'docs', 'architecture', 'api', 'api-contracts.md')
ENDPOINTS = set(m[0] + ' ' + m[1] for m in re.findall(r'^#### (GET|POST|PUT|PATCH|DELETE) `([^`]+)`', api_doc, re.M))
schema = rd(ROOT, 'docs', 'architecture', 'database', 'schema.md')
TABLES = set(re.findall(r'^### `([a-z_]+)`', schema, re.M)) | {'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'}
adrs = set(re.findall(r'ADR-\d\d\d', ' '.join(os.listdir(os.path.join(ROOT, 'docs', 'architecture', 'adr')))))
new_q = {q for q, d in questions.items() if 'new' in d['status']}
ALL_Q = known_q | new_q

PRI_RANK = {'Must': 0, 'Should': 1, 'Could': 2, "Won't (this spec)": 3}
SPRINT_NO = lambda s: 99 if s == 'B' else int(s)
SPRINT_LABEL = lambda s: 'Backlog (unscheduled)' if s == 'B' else 'S' + s

def story_text(s):
    return ' '.join([s['title'], s.get('role', ''), s.get('want', ''), s.get('so', ''), s['note'], s['prio'], s.get('p04', '')]
                    + [t for _, t in s['ac']] + s['be'] + s['bt'] + s['fe'] + s['ft'] + s['inf'])

# ---------- validation ----------
checks = []
def check(name, problems):
    checks.append((name, 'Pass' if not problems else 'FAIL', problems))

check('Every story cites at least one requirement, and every cited requirement exists in SRS.md',
      [f"{s['id']}: {r}" for s in stories.values() for r in (s['reqs'] or ['<none>']) if r not in REQ])
covered = {r for s in stories.values() for r in s['reqs']}
check('Every requirement that is not OUT OF SCOPE is covered by at least one story',
      [r for r, d in REQ.items() if d['cls'] != 'OUT OF SCOPE' and r not in covered])
check('No story cites an OUT OF SCOPE requirement (FR-OOS-01 checkout/payment M21, FR-OOS-02)',
      [f"{s['id']}: {r}" for s in stories.values() for r in s['reqs'] if REQ.get(r, {}).get('cls') == 'OUT OF SCOPE'])
check('Every dependency points to an existing story',
      [f"{s['id']} -> {d}" for s in stories.values() for d in s['deps'] if d not in stories])
# cycles
state, cyc = {}, []
def dfs(n, path):
    state[n] = 1
    for d in stories[n]['deps']:
        if d not in stories: continue
        if state.get(d) == 1: cyc.append(' -> '.join(path + [d]))
        elif not state.get(d): dfs(d, path + [d])
    state[n] = 2
for n in stories:
    if not state.get(n): dfs(n, [n])
check('The dependency graph has no cycles', cyc)
check("Each story's dependencies are scheduled in the same or an earlier sprint",
      [f"{s['id']} (S{s['sprint']}) depends on {d} ({SPRINT_LABEL(stories[d]['sprint'])})" for s in stories.values() for d in s['deps']
       if d in stories and SPRINT_NO(stories[d]['sprint']) > SPRINT_NO(s['sprint'])])
bad = []
for s in stories.values():
    classes = {REQ[r]['cls'] for r in s['reqs'] if r in REQ}
    weak = sorted(classes & {'HYPOTHESIS', 'UNKNOWN'})
    if s['status'] == 'Ready' and weak: bad.append(f"{s['id']}: Ready but cites {weak}")
    if 'UNKNOWN' in classes and s['status'] != 'Blocked': bad.append(f"{s['id']}: UNKNOWN requirement but status {s['status']}")
    if 'HYPOTHESIS' in classes and s['cls'] != 'Blocked': bad.append(f"{s['id']}: HYPOTHESIS requirement but class {s['cls']}")
    if s['status'] == 'Ready' and s['qs']: bad.append(f"{s['id']}: Ready with open questions {s['qs']}")
    if s['status'] != 'Ready' and not s['qs']: bad.append(f"{s['id']}: {s['status']} without a question ID")
check('No UNKNOWN or HYPOTHESIS requirement is treated as confirmed (status and class rules)', bad)
bad = []
for s in stories.values():
    top = min((PRI_RANK[REQ[r]['pri']] for r in s['reqs'] if r in REQ), default=0)
    if PRI_RANK[s['pri']] != top and not s['prio']: bad.append(f"{s['id']}: {s['pri']} vs requirement priority {list(PRI_RANK)[top]} with no stated reason")
check('Story priority equals the highest Phase 04 priority of its requirements, unless a reason is stated', bad)
bad = []
for s in stories.values():
    n = SPRINT_NO(s['sprint'])
    if s['pri'] == 'Must' and s['cls'] != 'MVP': bad.append(f"{s['id']}: Must but class {s['cls']}")
    if s['cls'] == 'MVP' and n > 5 and s['status'] != 'Blocked': bad.append(f"{s['id']}: MVP but scheduled {SPRINT_LABEL(s['sprint'])}")
    if s['cls'] == 'MVP (pulled in)' and (n > 5 or not s['note'].startswith('Pulled')): bad.append(f"{s['id']}: pulled in without a reason or outside S0-S5")
    if s['cls'] == 'Post-MVP' and n <= 5: bad.append(f"{s['id']}: Post-MVP scheduled in the MVP sprints")
check('MVP classes agree with priority and sprint (Must = MVP in S0–S5 unless Blocked; pulled-in items carry a reason)', bad)
check('Every API reference is one of the endpoints in api-contracts.md',
      [f"{s['id']}: {a}" for s in stories.values() for a in s['api'] if a not in ENDPOINTS])
check('Every table reference exists in schema.md (domain or framework tables)',
      [f"{s['id']}: {t}" for s in stories.values() for t in s['tables'] if t not in TABLES])
check('No story touches checkout, payment or order endpoints or tables (M21 stays excluded)',
      [f"{s['id']}: {x}" for s in stories.values() for x in s['api'] + s['tables'] if re.search(r'checkout|payment|order', x)])
alltext = ' '.join(story_text(s) for s in stories.values()) + ' '.join(' '.join(d.values()) for d in list(sprints.values()) + list(risks.values()) + list(questions.values()))
check('Every Q-ID cited exists in open-questions.md or is a new Phase 06 question (Q-80–Q-86)',
      sorted({q for q in re.findall(r'\bQ-\d\d\b', alltext) if q not in ALL_Q} | {q for s in stories.values() for q in s['qs'] if q not in ALL_Q}))
check('Every AC-ID cited exists in acceptance-criteria.md', sorted({a for a in re.findall(r'AC-[A-Z]+-[A-Z0-9]+-\d\d-\d', alltext) if a not in acs}))
check('Every BR-, C- and ADR- reference exists', sorted({x for x in re.findall(r'\bBR-\d\d-\d\d\b', alltext) if x not in brs}
      | {x for x in re.findall(r'\bC-\d\d\b', alltext) if x not in known_c} | {x for x in re.findall(r'ADR-\d\d\d', alltext) if x not in adrs}))
check('Every risk, sprint and story reference in the sprint plan and risk register exists',
      [r for sp in sprints.values() for r in re.findall(r'R-\d\d', sp['risks']) if r not in risks]
      + [u for rk in risks.values() for u in re.findall(r'US-\d\d\d', rk['stories']) if u not in stories]
      + [e for rk in risks.values() for e in re.findall(r'E\d\d', rk['epics']) if e not in epics])
check('Every story has acceptance criteria, and every story that isn\'t Blocked has at least one task',
      [s['id'] for s in stories.values() if not s['ac'] or (s['status'] != 'Blocked' and not any(s[k] for k in ('be', 'bt', 'fe', 'ft', 'inf')))])
check('Every story names its Phase 04 story or "new", and every Phase 04 story US-01–US-27 is carried forward',
      [s['id'] for s in stories.values() if not s.get('p04')] + [f'US-{n:02d}' for n in range(1, 28) if not any(re.search(rf'\bUS-{n:02d}\b', s.get('p04', '')) for s in stories.values())])
check('Every story is in a known epic and sprint', [s['id'] for s in stories.values() if s['epic'] not in epics or (s['sprint'] != 'B' and 'S' + s['sprint'] not in sprints)])

used_api = {a for s in stories.values() for a in s['api']}
check('Every endpoint in api-contracts.md is used by at least one story', sorted(ENDPOINTS - used_api))
# transitive Blocked ancestors: a story can only start once none of its ancestors is Blocked
def blocked_anc(sid, seen=None):
    seen = set() if seen is None else seen
    for d in stories[sid]['deps']:
        if d in stories and d not in seen:
            seen.add(d); blocked_anc(d, seen)
    return seen
for s in stories.values():
    s['waits'] = sorted(a for a in blocked_anc(s['id']) if stories[a]['status'] == 'Blocked')
    s['startable'] = s['status'] != 'Blocked' and not s['waits']
check('Every story reported as startable has no Blocked story among its transitive dependencies',
      [s['id'] for s in stories.values() if s['startable'] and any(stories[a]['status'] == 'Blocked' for a in blocked_anc(s['id']))])
def waits_text(s):
    return '; '.join(a + ' (' + ', '.join(stories[a]['qs']) + ')' for a in s['waits']) or '—'

for n, r, p in checks:
    print(r, '-', n, p[:10] if p else '')

# ---------- helpers ----------
def link_req(r): return r
def tasks(s):
    out = []
    for key, label in (('be', 'B'), ('bt', 'BT'), ('fe', 'F'), ('ft', 'FT'), ('inf', 'I')):
        for i, t in enumerate([t for t in s[key] if not t.startswith('None')], 1):
            out.append((key, f"T-{s['id'][3:]}-{label}{i}", t))
    return out
def qlist(s): return ', '.join(s['qs']) or '—'
def reqcls(s): return ', '.join(f"{r} ({REQ[r]['cls']})" for r in s['reqs'])
order = sorted(stories.values(), key=lambda s: (SPRINT_NO(s['sprint']), PRI_RANK[s['pri']], s['id']))

def tpl(name):
    t = rd(HERE, 'templates.md')
    m = re.search(r'^=== ' + re.escape(name) + r' ===\n(.*?)(?=^=== |\Z)', t, re.M | re.S)
    return (m.group(1).replace('{{NREQ}}', str(len(REQ))).replace('{{NR}}', str(len(risks)))
            .replace('{{NE}}', str(len(epics))).replace('{{NQ}}', str(len(questions))))

def write(name, text):
    os.makedirs(OUT, exist_ok=True)
    open(os.path.join(OUT, name), 'w', encoding='utf-8', newline='\n').write(text.rstrip() + '\n')

# ---------- 01 MVP scope ----------
def req_mvp(r, d):
    sts = [s for s in stories.values() if r in s['reqs']]
    ids = ', '.join(s['id'] for s in sts) or '—'
    qs = sorted({q for s in sts for q in s['qs']})
    if d['cls'] == 'OUT OF SCOPE': return 'Out of scope', 'Excluded by the project scope (M21 checkout and payment, or the reference group\'s rewards programme). Not designed', ids
    if d['cls'] == 'HYPOTHESIS': return 'Blocked by unresolved decision', f"HYPOTHESIS: built only behind a switch or as its own story, pending PO validation ({', '.join(qs)})", ids
    if d['cls'] == 'UNKNOWN': return 'MVP, blocked by unresolved decision', f"Must placeholder for an UNKNOWN requirement; the value or target must be decided first ({', '.join(qs)})", ids
    if d['pri'] == 'Must':
        gated = [s for s in sts if s['status'] != 'Ready']
        why = 'CONFIRMED Must: part of the core journey J1, brand search J4 or a legal, security or localisation baseline'
        if gated: why += f"; acceptance gated by {', '.join(sorted({q for s in gated for q in s['qs']}))}"
        return 'MVP', why, ids
    early = [s for s in sts if SPRINT_NO(s['sprint']) <= 5]
    if early:
        s0 = early[0]
        why = s0['note'].replace('Pulled into the MVP sprints: ', '') if s0['cls'] == 'MVP (pulled in)' else f"bundled in the Must story {s0['id']}"
        return 'MVP (pulled in)', f"CONFIRMED {d['pri']}, kept at that priority but delivered in the MVP sprints: {why}", ids
    return 'Post-MVP', f"CONFIRMED {d['pri']}: secondary to the core journey (PRD §8 rule)", ids
rows = []
cnt = collections.Counter()
for r, d in REQ.items():
    c, why, ids = req_mvp(r, d)
    cnt[c] += 1
    rows.append(f"| {r} | {d['cls']} | {d['pri']} | **{c}** | {why} | {ids} |")
t01 = tpl('01').replace('{{REQ_TABLE}}', '| Requirement | Phase 04 class | Phase 04 priority | MVP classification | Reason | Stories |\n|---|---|---|---|---|---|\n' + '\n'.join(rows))
rc = collections.Counter(d['cls'] for d in REQ.values())
t01 = t01.replace('{{REQ_CLASSES}}', f"{len(REQ)} requirements: " + ', '.join(f'{v} {k}' for k, v in rc.most_common()))
t01 = t01.replace('{{REQ_COUNTS}}', '; '.join(f'{k}: {v}' for k, v in sorted(cnt.items())))
feat = collections.defaultdict(list)
for s in stories.values(): feat[s['cls']].append(f"{s['id']} {s['title']}")
t01 = t01.replace('{{FEATURES}}', '\n'.join(f"- **{k}** ({len(v)}): " + '; '.join(v) + '.' for k, v in sorted(feat.items())))
write('01-mvp-scope.md', t01)

# ---------- 02 epics ----------
rows = []
for e in epics.values():
    sts = [s for s in stories.values() if s['epic'] == e['id']]
    reqs = sorted({r for s in sts for r in s['reqs']}, key=list(REQ).index)
    mvp = sum(1 for s in sts if s['cls'].startswith('MVP'))
    rows.append(f"| {e['id']} | {e['name']} | {e['goal']} | {e['modules']} | {', '.join(s['id'] for s in sts)} | {mvp} of {len(sts)} | {', '.join(reqs)} |")
write('02-epics.md', tpl('02') + '\n| Epic | Name | Goal | Phase 05 modules | Stories | MVP stories | Requirements covered |\n|---|---|---|---|---|---|---|\n' + '\n'.join(rows))

# ---------- 03 product backlog ----------
rows = [f"| {i} | {s['id']} | {s['title']} | {s['epic']} | {s['pri']} | {s['status']} | {'yes' if s['startable'] else 'no'} | {s['cls']} | {SPRINT_LABEL(s['sprint'])} | {', '.join(s['deps']) or '—'} | {waits_text(s)} | {', '.join(s['reqs'])} | {qlist(s)} |"
        for i, s in enumerate(order, 1)]
mos = collections.Counter(s['pri'] for s in stories.values()); st = collections.Counter(s['status'] for s in stories.values()); cl = collections.Counter(s['cls'] for s in stories.values())
summary = ('| Measure | Count |\n|---|---|\n' + f"| Stories | {len(stories)} |\n"
           + ''.join(f"| MoSCoW {k} | {mos[k]} |\n" for k in ('Must', 'Should', 'Could'))
           + ''.join(f"| Status {k} | {st[k]} |\n" for k in ('Ready', 'Needs Clarification', 'Blocked'))
           + ''.join(f"| Class {k} | {cl[k]} |\n" for k in ('MVP', 'MVP (pulled in)', 'Post-MVP', 'Blocked')))
write('03-product-backlog.md', tpl('03').replace('{{SUMMARY}}', summary) + '\n| Rank | ID | Title | Epic | MoSCoW | Status | Startable | MVP class | Sprint | Dependencies | Waits on (Blocked ancestors) | Source requirements | Open questions |\n|---|---|---|---|---|---|---|---|---|---|---|---|---|\n' + '\n'.join(rows))

# ---------- 04 user stories ----------
out = [tpl('04')]
for e in epics.values():
    out.append(f"\n## {e['id']} — {e['name']}\n")
    for s in [s for s in stories.values() if s['epic'] == e['id']]:
        b = [f"### {s['id']} — {s['title']}",
             f"- **ID:** {s['id']} · **Epic:** {s['epic']} · **Phase 04 story:** {s['p04']}",
             '', f"> As a {s['role']},  ", f"> I want {s['want']},  ", f"> so that {s['so']}.", '',
             f"- **Priority:** {s['pri']}" + (f" ({s['prio']})" if s['prio'] else ''),
             f"- **Status:** {s['status']}" + (f" ({', '.join(s['qs'])})" if s['qs'] else ''),
             f"- **MVP class:** {s['cls']} · **Sprint:** {SPRINT_LABEL(s['sprint'])}",
             f"- **Dependencies:** {', '.join(s['deps']) or 'none'}",
             f"- **Waits on (Blocked ancestors):** {waits_text(s)} · **Startable once its dependencies are done:** {'yes' if s['startable'] else 'no'}",
             f"- **Source requirements:** {reqcls(s)}",
             f"- **API:** {'; '.join('`' + a + '`' for a in s['api']) or 'none'}",
             f"- **Tables:** {', '.join('`' + t + '`' for t in s['tables']) or 'none'}"]
        if s['note']: b.append(f"- **Note:** {s['note']}")
        b += ['', '**Acceptance criteria**']
        b += [f"{i}. *{k}.* {t}" for i, (k, t) in enumerate(s['ac'], 1)]
        tk = tasks(s)
        b += ['', f"**Tasks:** {', '.join(x[1] for x in tk) if tk else 'none until the open questions are answered'} ([05-technical-tasks.md](05-technical-tasks.md#{s['id'].lower()}))", '']
        out.append('\n'.join(b))
write('04-user-stories.md', '\n'.join(out))

# ---------- 05 technical tasks ----------
out = [tpl('05')]
GROUPS = (('Backend', ('be', 'bt')), ('Frontend', ('fe', 'ft')), ('Infrastructure', ('inf',)))
for e in epics.values():
    out.append(f"\n## {e['id']} — {e['name']}\n")
    for s in [s for s in stories.values() if s['epic'] == e['id']]:
        tk = tasks(s)
        out.append(f"### {s['id']}\n**{s['title']}** · {s['status']} · {SPRINT_LABEL(s['sprint'])}" + (f" · API: {'; '.join('`' + a + '`' for a in s['api'])}" if s['api'] else '') + '\n')
        if not tk and not any(t.startswith('None') for k in ('be', 'fe', 'inf') for t in s[k]):
            out.append('No tasks: the behaviour is not designed and must not be invented (see the story note).\n')
            continue
        for gname, keys in GROUPS:
            items = [x for x in tk if x[0] in keys]
            notes = [t for k in keys for t in s[k] if t.startswith('None')]
            if items or notes:
                out.append(f"- **{gname}:**")
                out += [f"  - {tid}{' (test)' if k in ('bt', 'ft') else ''}: {t}" for k, tid, t in items]
                out += [f"  - *No task:* {t}" for t in notes]
        out.append('')
write('05-technical-tasks.md', '\n'.join(out))

# ---------- 06 sprint planning ----------
out = [tpl('06')]
for sid, sp in sprints.items():
    sts = [s for s in order if 'S' + s['sprint'] == sid]
    tk = [(s, x) for s in sts for x in tasks(s)]
    ext = sorted({d for s in sts for d in s['deps'] if d in stories and 'S' + stories[d]['sprint'] != sid}, key=lambda x: x)
    def tl(keys): return ', '.join(x[1] for s, x in tk if x[0] in keys) or '—'
    out.append(f"\n## {sid} — {sp['name']}\n")
    out.append(f"- **Sprint goal:** {sp['goal']}")
    out.append(f"- **Decision gate:** {sp['gate']}")
    out.append(f"- **User stories:** " + '; '.join(f"{s['id']} {s['title']} ({s['pri']}, {s['status']})" for s in sts))
    out.append(f"- **Backend tasks:** {tl(('be',))}")
    out.append(f"- **Frontend tasks:** {tl(('fe',))}")
    out.append(f"- **Testing tasks:** {tl(('bt', 'ft'))}")
    out.append(f"- **Infrastructure tasks:** {tl(('inf',))}")
    extl = ', '.join(d + ' (' + SPRINT_LABEL(stories[d]['sprint']) + ')' for d in ext) or 'none'
    out.append(f"- **Dependencies on earlier sprints:** {extl}")
    out.append(f"- **Expected deliverables:** {sp['deliverables']}")
    out.append(f"- **Risks:** " + ', '.join(f"{r} ({risks[r]['desc'].split('.')[0]})" for r in re.findall(r'R-\d\d', sp['risks'])))
blk = [s for s in stories.values() if s['sprint'] == 'B']
out.append('\n## Unscheduled backlog\n')
out += [f"- **{s['id']} {s['title']}** ({s['status']}: {', '.join(s['qs'])}). {s['note']}" for s in blk]
write('06-sprint-planning.md', '\n'.join(out))

# ---------- 08 risk register ----------
rows = [f"| {r['id']} | {r['desc']} | {r['prob']} | {r['impact']} | {r['mitigation']} | {r['epics']} | {r['stories']} | {r['owner']} |" for r in risks.values()]
write('08-risk-register.md', tpl('08') + '\n| Risk ID | Description | Probability | Impact | Mitigation | Related epic | Related stories | Owner role |\n|---|---|---|---|---|---|---|---|\n' + '\n'.join(rows))

# ---------- 09 open questions ----------
rows = []
for q in questions.values():
    aff = [s['id'] for s in stories.values() if q['id'] in s['qs'] or re.search(r'\b' + q['id'] + r'\b', story_text(s))]
    rows.append(f"| {q['id']} | {q['q']} | {q['why']} | {q['impact']} | {', '.join(aff) or 'no story (not designed)'} | {q['owner']} | {q['status']} |")
write('09-open-questions.md', tpl('09') + '\n| ID | Question | Why it matters | Impact on implementation | Affected user stories | Responsible stakeholder | Status |\n|---|---|---|---|---|---|---|\n' + '\n'.join(rows))

# ---------- 10 summary ----------
vt = '| # | Check | Result |\n|---|---|---|\n' + '\n'.join(f"| {i} | {n} | **{r}**{': ' + '; '.join(p[:8]) if p else ''} |" for i, (n, r, p) in enumerate(checks, 1))
by_status = '| Status | Must | Should | Could | Total |\n|---|---|---|---|---|\n' + '\n'.join(
    f"| {k} | " + ' | '.join(str(sum(1 for s in stories.values() if s['status'] == k and s['pri'] == p)) for p in ('Must', 'Should', 'Could')) + f" | {st[k]} |" for k in ('Ready', 'Needs Clarification', 'Blocked'))
mvp_sts = [s for s in stories.values() if s['cls'].startswith('MVP')]
mvp_line = f"{len(mvp_sts)} stories are in the MVP scope ({cl['MVP']} MVP, {cl['MVP (pulled in)']} pulled in): {sum(1 for s in mvp_sts if s['status'] == 'Ready')} Ready, {sum(1 for s in mvp_sts if s['status'] == 'Needs Clarification')} Needs Clarification, {sum(1 for s in mvp_sts if s['status'] == 'Blocked')} Blocked."
startable = [s for s in stories.values() if s['startable']]
q70 = [s for s in mvp_sts if s['id'] in ('US-012', 'US-015') or set(s['waits']) & {'US-012', 'US-015'}]
mvp_line += (f" Of all {len(stories)} stories, **{len(startable)} are startable** (not Blocked, with no Blocked ancestor), and {len(stories) - len(startable)} are Blocked or wait on a Blocked story."
             f" **{len(q70)} of the {len(mvp_sts)} MVP stories wait on Q-70**, directly or through US-012 and US-015.")
s0 = '; '.join(s['id'] + ' ' + s['title'] for s in order if s['sprint'] == '0' and s['startable'] and s['status'] == 'Ready')
t10 = tpl('10').replace('{{S0_READY}}', s0).replace('{{VALIDATION}}', vt).replace('{{BY_STATUS}}', by_status).replace('{{MVP_LINE}}', mvp_line).replace('{{N}}', str(len(stories))).replace('{{T}}', str(sum(len(tasks(s)) for s in stories.values())))
write('10-phase-06-summary.md', t10)

print(len(stories), 'stories;', sum(len(tasks(s)) for s in stories.values()), 'tasks;', dict(st), dict(cl))
sys.exit(0 if all(r == 'Pass' for _, r, _ in checks) else 1)
