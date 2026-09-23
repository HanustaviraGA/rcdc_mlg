(() => {
"use strict";
// The reference provides the presentation and guide, never lecturer records.
const source = window.publicationDashboard;
const GUIDE = {
    "cluster": {
        "A": {
            "kriteria": [
                "Pendidikan S3 atau JJA Lektor/LK/GB",
                "Memiliki publikasi Scopus",
                "Pernah menjadi ketua riset eksternal"
            ],
            "hibah": [
                "PIB-TERAPAN",
                "PIB-DASAR",
                "BBD-UN",
                "BPL-TTG"
            ]
        },
        "B": {
            "kriteria": [
                "Pendidikan S3 atau JJA Lektor/LK/GB",
                "Memiliki publikasi Scopus",
                "Belum pernah menjadi ketua riset eksternal"
            ],
            "hibah": [
                "PIB-TERAPAN",
                "PIB-DASAR",
                "BBD-UN",
                "BPL-TTG"
            ]
        },
        "C": {
            "kriteria": [
                "Belum memenuhi kriteria Cluster A & B",
                "Menjadi member RIG / Research Center"
            ],
            "hibah": [
                "PPB",
                "BBD-UN",
                "BPL-TTG"
            ]
        }
    },
    "matriks": [
        {
            "kolom": "AA S2",
            "f3": 0.5,
            "f4": 1,
            "f5": "Skor 4 + bobot ≥ 1,5",
            "f6": 1,
            "p3": 0.25,
            "p4": 0.5,
            "p5": "Skor 4 + paper jurnal Scopus",
            "p6": 0.25
        },
        {
            "kolom": "L S2",
            "f3": 0.5,
            "f4": 1,
            "f5": "Skor 4 + paper jurnal Scopus",
            "f6": 1,
            "p3": 0.25,
            "p4": 0.5,
            "p5": "Skor 4 + paper jurnal Scopus",
            "p6": 0.5
        },
        {
            "kolom": "AA S3 / TP S3 / LK S2",
            "f3": 1,
            "f4": 2,
            "f5": "Skor 4 + paper jurnal Scopus",
            "f6": 1.5,
            "p3": 0.75,
            "p4": 1,
            "p5": "Skor 4 + paper jurnal Scopus",
            "p6": 1
        },
        {
            "kolom": "L S3 / LK S3 (F) · L S3 (P)",
            "f3": 3,
            "f4": 4,
            "f5": "Skor 4 + paper jurnal Scopus",
            "f6": 2,
            "p3": 1,
            "p4": 2,
            "p5": "Skor 4 + paper jurnal Scopus",
            "p6": 1
        },
        {
            "kolom": "GB",
            "f3": 5,
            "f4": 6,
            "f5": "Skor 4 + paper jurnal Scopus",
            "f6": 2,
            "p3": null,
            "p4": null,
            "p5": null,
            "p6": null
        }
    ]
};
const YEARS = source.years.map(String).sort();
const HYEARS = [...new Set(source.grants.map(grant => String(grant.year)))].sort();
const yearRange = years => !years.length ? 'belum tersedia' : years.length === 1 ? years[0] : years[0] + '–' + years.at(-1);
const programKeys = new Map(), PRODI = Object.create(null);
for (const name of [...new Set(source.faculty.map(row => row.program))].sort()) {
    const member = source.faculty.find(row => row.program === name && row.program_code);
    const preferred = member?.program_code || name.split(/\s+/).map(word => word[0]).join('').slice(0, 8);
    let key = preferred, suffix = 2;
    while (Object.hasOwn(PRODI, key)) key = preferred + ' ' + suffix++;
    programKeys.set(name, key);
    PRODI[key] = name;
}
const columns = {TP12: 'TP S1 / TP S2', AA2: 'AA S2', L2: 'L S2', AA3TP3LK2: 'AA S3 / TP S3 / LK S2', L3LK3: 'L S3 / LK S3', L3: 'L S3', GB: 'GB'};
const ranks = {TP: 'Tenaga Pengajar', AA: 'Asisten Ahli', L: 'Lektor', LK: 'Lektor Kepala', GB: 'Guru Besar'};
const DOSEN = source.faculty.map(row => {
    const grants = source.grants.filter(grant => grant.code === row.code);
    const chairs = grants.filter(grant => String(grant.role).trim().toLowerCase() === 'ketua');
    const members = grants.filter(grant => String(grant.role).trim().toLowerCase().startsWith('anggota'));
    const plans = source.priorities.filter(plan => plan.code === row.code);
    return {
        kode: row.code, nama: row.name, prodi: programKeys.get(row.program),
        tipe: row.faculty_detail || row.faculty || 'Tidak tercatat',
        jenjang: row.education || 'Tidak terpetakan', jabatan: ranks[row.rank] || 'Tidak terpetakan',
        kolom: columns[row.rule?.function?.replace(/Func$|Prof$/, '')] || 'Tidak terpetakan',
        ambang4num: row.rule?.threshold ?? null, annual: row.annual,
        get cluster() { return row.annual[S.year]?.cluster || 'Tidak tercatat'; },
        get labelMentor() { return row.annual[S.year]?.mentor?.toUpperCase() || 'Tidak tercatat'; },
        ketua: HYEARS.map(year => chairs.filter(grant => String(grant.year) === year).length),
        anggota: HYEARS.map(year => members.filter(grant => String(grant.year) === year).length),
        totKetua: chairs.length, totAnggota: members.length, totHibah: chairs.length + members.length,
        statusKeterlibatan: chairs.length ? 'Pernah menjadi ketua' : members.length ? 'Hanya sebagai anggota' : 'Tidak tercatat',
        sdg: [...new Set(plans.flatMap(plan => plan.sdgs))], topik: plans.map(plan => plan.topic).join('\n\n')
    };
});
const D = {dosen: DOSEN, prodiNama: PRODI, ...GUIDE};


/* ---------- helper ---------- */
const $ = id => document.getElementById(id);
const esc = s => String(s==null?"":s).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c]));
const n1 = x => (x==null||isNaN(x))?"–":(Math.round(x*10)/10).toLocaleString("id-ID",{minimumFractionDigits:1,maximumFractionDigits:1});
const n0 = x => (x==null||isNaN(x))?"–":Math.round(x).toLocaleString("id-ID");
const n2 = x => (x==null||isNaN(x))?"–":(Math.round(x*100)/100).toLocaleString("id-ID",{minimumFractionDigits:2,maximumFractionDigits:2});
const MAXSCORE = 6, STANDAR = 4;
const pct = s => s==null?null:(s/MAXSCORE)*100;
const shortName = s => String(s).split(",")[0].replace(/^(Dr\.|Ir\.|Prof\.)\s*/,"").trim();
const HALO = 'style="paint-order:stroke;stroke:#fff;stroke-width:3.5px;stroke-linejoin:round"';

const CAT = [
  {k:"Excellent",        c:"#12764C", min:100, desc:"skor 6 dari 6"},
  {k:"Good",             c:"#2E77C4", min:75,  desc:"skor 5"},
  {k:"Moderate",         c:"#C89A20", min:50,  desc:"skor 3–4"},
  {k:"Need Improvement", c:"#A93226", min:0,   desc:"skor 0–2"}
];
const catOf = s => s==null?null:(CAT.find(c=>pct(s)>=c.min)||CAT[3]);
const catColor = k => (CAT.find(c=>c.k===k)||{}).c || "#93A3B5";
const tone = p => p>=100?"ok":p>=70?"warn":"bad";
const TONEC = {ok:"#12764C",warn:"#B5790C",bad:"#A93226"};
const LABC = {"MENTOR":"#12764C","KANDIDAT MENTOR":"#C89A20","MENTEE":"#A93226","Tidak tercatat":"#93A3B5"};
const HIBC = {"Pernah menjadi ketua":"#12764C","Hanya sebagai anggota":"#2E77C4","Belum pernah terlibat":"#A93226","Tidak tercatat":"#93A3B5"};


/* ---------- state ---------- */
const S = {year:source.default_year || "", prodi:"", type:"", pend:"", jja:"", clu:"", lab:"", dosen:"", topN:10, q:""};
const scoreOf = (d,y) => d.annual[y]?.score ?? null;
const passFilters = d =>
     (!S.prodi || d.prodi===S.prodi) && (!S.type || d.tipe===S.type)
  && (!S.pend  || d.jenjang===S.pend) && (!S.jja || d.jabatan===S.jja)
  && (!S.clu   || d.cluster===S.clu)  && (!S.lab || d.labelMentor===S.lab)
  && (!S.dosen || d.kode===S.dosen);
const scope = () => DOSEN.filter(passFilters);
const avgOf = (list,y) => { const v=list.map(d=>scoreOf(d,y)).filter(x=>x!=null); return v.length? v.reduce((a,b)=>a+b,0)/v.length : null; };

/* ---------- tooltip ---------- */
const tipEl = $("tip");
function tooltipContent(html) {
  const template = document.createElement('template');
  template.innerHTML = html;
  const copy = node => {
    if (node.nodeType === Node.TEXT_NODE) return document.createTextNode(node.textContent);
    if (!['B', 'S', 'BR'].includes(node.nodeName)) return document.createTextNode(node.textContent || '');
    const clean = document.createElement(node.nodeName.toLowerCase());
    clean.append(...Array.from(node.childNodes, copy));
    return clean;
  };
  return Array.from(template.content.childNodes, copy);
}
function bindTip(el, html){
  const move = e => {
    const r = tipEl.getBoundingClientRect();
    let x = e.clientX+14, y = e.clientY-10;
    if(x+r.width > innerWidth-8) x = e.clientX-r.width-14;
    if(y+r.height > innerHeight-8) y = innerHeight-r.height-8;
    tipEl.style.left = x+"px"; tipEl.style.top = Math.max(8,y)+"px";
  };
  el.addEventListener("mouseenter", e=>{ tipEl.replaceChildren(...tooltipContent(html)); tipEl.style.opacity=1; move(e); });
  el.addEventListener("mousemove", move);
  el.addEventListener("mouseleave", ()=>{ tipEl.style.opacity=0; });
}
const tips = root => root.querySelectorAll("[data-tip]").forEach(el=>bindTip(el, el.getAttribute("data-tip")));

/* ---------- filters ---------- */
const uniq = fn => [...new Set(DOSEN.map(fn).filter(Boolean))];
const fill = (sel, items, allLabel) =>
  sel.innerHTML = `<option value="">${allLabel}</option>` + items.map(v=>`<option value="${esc(v.v)}">${esc(v.t)}</option>`).join("");

function buildFilters(){
  $("fYear").innerHTML = YEARS.slice().reverse().map(y=>`<option value="${esc(y)}"${y===S.year?" selected":""}>${esc(y)}</option>`).join("") || '<option value="">Belum ada snapshot</option>';
  fill($("fProdi"), Object.keys(PRODI).filter(k=>DOSEN.some(d=>d.prodi===k)).map(k=>({v:k,t:`${k} — ${PRODI[k]}`})), "Semua prodi");
  fill($("fType"),  uniq(d=>d.tipe).sort().map(v=>({v,t:v})), "Semua tipe");
  fill($("fPend"),  uniq(d=>d.jenjang).sort().map(v=>({v,t:v})), "Semua jenjang");
  fill($("fJJA"),   uniq(d=>d.jabatan).sort().map(v=>({v,t:v})), "Semua jabatan");
  refreshProfileFilters();
  refreshDosenList();
  const on = (id,k)=>$(id).addEventListener("change",e=>{
    S[k]=e.target.value;
    if(k==="year") refreshProfileFilters();
    if(k!=="dosen") refreshDosenList();
    render();
  });
  ["fYear|year","fProdi|prodi","fType|type","fPend|pend","fJJA|jja","fClu|clu","fLab|lab","fDosen|dosen"]
    .forEach(s=>{ const [id,k]=s.split("|"); on(id,k); });
  $("fReset").addEventListener("click",()=>{
    ["prodi","type","pend","jja","clu","lab","dosen"].forEach(k=>S[k]="");
    ["fProdi","fType","fPend","fJJA","fClu","fLab"].forEach(i=>$(i).value="");
    S.q=""; $("fTopik").value="";
    refreshDosenList(); render();
  });
  $("topToggle").addEventListener("click",e=>{
    const b=e.target.closest("button"); if(!b) return;
    S.topN=+b.dataset.n;
    [...$("topToggle").children].forEach(x=>x.setAttribute("aria-pressed", x===b));
    renderTop();
  });
  $("fTopik").addEventListener("input",e=>{ S.q=e.target.value.trim().toLowerCase(); renderTopik(); });
  $("printDashboard").addEventListener("click",()=>window.print());
  $("stamps").innerHTML = [[DOSEN.length, 'Faculty member'], [Object.keys(PRODI).length, 'Program studi'], [YEARS.join(' · ') || 'Belum tersedia', 'Tahun publikasi']]
    .map(([value,label])=>`<div class="stamp"><b class="num">${esc(value)}</b><span>${esc(label)}</span></div>`).join('');
  $("trendTitle").textContent = 'Perkembangan KPI' + (YEARS.length ? ' ' + yearRange(YEARS) : '');
  $("grantTitle").textContent = 'Peran dalam hibah penelitian' + (HYEARS.length ? ' ' + yearRange(HYEARS) : '');
}
function refreshProfileFilters(){
  [['fClu','clu',d=>d.cluster,'Semua cluster'], ['fLab','lab',d=>d.labelMentor,'Semua label']].forEach(([id,key,read,label])=>{
    const values=uniq(read).sort();
    fill($(id),values.map(v=>({v,t:key==='clu' && v!=='Tidak tercatat' ? 'Cluster '+v : v})),label);
    if(!values.includes(S[key])) S[key]='';
    $(id).value=S[key];
  });
}
function refreshDosenList(){
  const keep=S.dosen;
  const list = DOSEN.filter(d=>(!S.prodi||d.prodi===S.prodi)&&(!S.type||d.tipe===S.type)&&(!S.pend||d.jenjang===S.pend)
      &&(!S.jja||d.jabatan===S.jja)&&(!S.clu||d.cluster===S.clu)&&(!S.lab||d.labelMentor===S.lab))
    .sort((a,b)=>a.nama.localeCompare(b.nama,"id"));
  fill($("fDosen"), list.map(d=>({v:d.kode,t:d.nama.length>38?d.nama.slice(0,36)+"…":d.nama})), "Semua dosen");
  if(list.some(d=>d.kode===keep)) $("fDosen").value=keep; else S.dosen="";
}

/* ---------- 01 KPI cards ---------- */
function renderKPI(){
  const rows = scope(), y = S.year;
  const scored = rows.map(d=>scoreOf(d,y)).filter(v=>v!=null);
  const avg = scored.length ? scored.reduce((a,b)=>a+b,0)/scored.length : null;
  const avgPct = avg==null?null:pct(avg);
  const memenuhi = rows.filter(d=>{const s=scoreOf(d,y); return s!=null && s>=STANDAR;}).length;
  const achPct = scored.length ? memenuhi/scored.length*100 : null;
  const risk = rows.filter(d=>{const s=scoreOf(d,y); return s!=null && pct(s)<50;}).length;
  const hibah = rows.reduce((a,d)=>a+d.totHibah,0);
  const ketua = rows.reduce((a,d)=>a+d.totKetua,0);
  const noData = rows.length - scored.length;
  const avgT = avgPct==null?"warn":tone(avgPct), achT = achPct==null?"warn":tone(achPct);

  const card = o => `<div class="kpi ${o.tone||''}">
      <div class="lab">${o.lab}</div>
      <div class="val num">${o.val}${o.unit?`<small> ${o.unit}</small>`:""}</div>
      ${o.bar!=null?`<div class="bar"><i style="width:${Math.min(100,o.bar)}%;background:${TONEC[o.tone]||'#5B98D8'}"></i></div>`:""}
      <div class="foot">${o.dotc?`<span class="dot" style="background:${o.dotc}"></span>`:""}${o.foot}</div></div>`;

  $("kpiRow").innerHTML = [
    card({lab:"Total dosen", val:n0(rows.length), foot:`${n0(scored.length)} punya skor ${y}${noData?` · ${n0(noData)} tanpa data`:""}`}),
    card({lab:"Program studi", val:n0(new Set(rows.map(d=>d.prodi)).size),
          foot: S.prodi?esc(PRODI[S.prodi]||S.prodi):"Seluruh prodi dalam cakupan"}),
    card({lab:`Rata-rata KPI ${y}`, val:n1(avg), unit:`/ ${MAXSCORE}`, tone:avgT, bar:avgPct, dotc:TONEC[avgT],
          foot:`${avgPct==null?"–":n1(avgPct)+"% dari skor penuh"}`}),
    card({lab:"Memenuhi standar matriks", val:achPct==null?"–":n1(achPct), unit:"%", tone:achT, bar:achPct, dotc:TONEC[achT],
          foot:`${n0(memenuhi)} dosen berskor ≥ ${STANDAR} pada ${y}`}),
    card({lab:"Keterlibatan hibah" + (HYEARS.length ? ' ' + yearRange(HYEARS) : ''), val:n0(hibah),
          foot:`${n0(ketua)} peran ketua · ${n1(rows.length?hibah/rows.length:0)} per dosen`}),
    card({lab:"Dosen di bawah 50%", val:n0(risk), tone: risk===0?"ok":(scored.length&&risk/scored.length>0.2?"bad":"warn"),
          bar: scored.length?risk/scored.length*100:0,
          foot:`${scored.length?n1(risk/scored.length*100):"0"}% dari dosen berskor ${y}`})
  ].join("");

  $("kpiSub").textContent = source.snapshots[y]?.has_kpi ? 'Skor mengikuti KPI Excel (RTTO), skala 0–6.' : 'Skor mengikuti perhitungan sistem, skala 0–6.';
  $("scope").innerHTML = `Cakupan aktif: <b class="num">${n0(rows.length)}</b> dosen · tahun <b>${y}</b>`
    + (noData?` · <b class="num">${n0(noData)}</b> tanpa skor`:"");
}

/* ---------- 02 line ---------- */
function renderTrend(){
  const W=660,H=300,mL=44,mR=124,mT=26,mB=36;
  const px = i => YEARS.length < 2 ? (W+mL-mR)/2 : mL + i*((W-mL-mR)/(YEARS.length-1));
  const py = v => mT + (1 - v/MAXSCORE)*(H-mT-mB);
  const rows = scope();
  if(!YEARS.length){ $("trend").innerHTML='<div class="empty">Belum ada snapshot KPI untuk grafik tren.</div>'; $("trendLeg").innerHTML=''; $("trendHint").textContent=''; return; }
  let s = `<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="Tren rata-rata skor KPI ${esc(yearRange(YEARS))}">`;
  for(let g=0; g<=6; g++){
    const yy=py(g);
    s += `<line class="gridline" x1="${mL}" y1="${yy}" x2="${W-mR}" y2="${yy}"/><text class="tick" x="${mL-9}" y="${yy+4}" text-anchor="end">${g}</text>`;
  }
  s += `<line x1="${mL}" y1="${py(STANDAR)}" x2="${W-mR}" y2="${py(STANDAR)}" stroke="#12764C" stroke-width="1.3" stroke-dasharray="6 4" opacity=".55"/>`;
  s += `<text class="tick" x="${W-mR-4}" y="${py(STANDAR)-6}" text-anchor="end" fill="#12764C" ${HALO}>standar matriks (4)</text>`;
  YEARS.forEach((yr,i)=> s+=`<text class="tick b" x="${px(i)}" y="${H-mB+20}" text-anchor="middle">${yr}</text>`);
  s += `<text class="alab" transform="translate(11,${(H-mB+mT)/2}) rotate(-90)" text-anchor="middle">Rata-rata skor KPI</text>`;

  if(!S.dosen){
    Object.keys(PRODI).filter(k=>DOSEN.some(d=>d.prodi===k)).forEach(k=>{
      if(S.prodi && S.prodi!==k) return;
      const list = DOSEN.filter(d=>d.prodi===k && passFiltersExceptProdi(d));
      const pts = YEARS.map((y,i)=>({i,y,v:avgOf(list,y)})).filter(p=>p.v!=null);
      if(pts.length<2) return;
      const dd = pts.map((p,j)=>`${j?"L":"M"}${px(p.i)},${py(p.v)}`).join(" ");
      const tip = `<b>${esc(PRODI[k]||k)}</b>` + pts.map(p=>`<s>${p.y}: rata-rata ${n1(p.v)}</s>`).join("");
      s += `<g data-tip="${tip}"><path d="${dd}" fill="none" stroke="transparent" stroke-width="12"/>`
         + `<path d="${dd}" fill="none" stroke="#9CBFE3" stroke-width="1.5" opacity=".9"/></g>`;
      const last = pts[pts.length-1];
      if(S.prodi) s += `<text class="tick" x="${px(last.i)+10}" y="${py(last.v)+4}" ${HALO}>${esc(k)}</text>`;
    });
  }
  const solid = YEARS.map((y,i)=>({i,y,v:avgOf(rows,y),n:rows.filter(d=>scoreOf(d,y)!=null).length})).filter(p=>p.v!=null);
  if(solid.length){
    const dd = solid.map((p,j)=>`${j?"L":"M"}${px(p.i)},${py(p.v)}`).join(" ");
    const area = `M${px(solid[0].i)},${py(0)} ` + solid.map(p=>`L${px(p.i)},${py(p.v)}`).join(" ") + ` L${px(solid[solid.length-1].i)},${py(0)} Z`;
    s += `<path d="${area}" fill="#2E77C4" opacity=".09"/><path d="${dd}" fill="none" stroke="#0E2E56" stroke-width="2.8" stroke-linejoin="round" stroke-linecap="round"/>`;
    solid.forEach((p,j)=>{
      const t=tone(pct(p.v)), first=j===0, last=j===solid.length-1;
      const lx = px(p.i) + (first?11:(last?-11:0));
      const ly = py(p.v) + (first||last?-9:(solid[j-1].v>p.v?20:-15));
      s += `<g data-tip="<b>${p.y}</b><s>Rata-rata skor ${n1(p.v)} dari 6 (${n1(pct(p.v))}%)</s><s>${n0(p.n)} dosen berskor</s>">
        <circle cx="${px(p.i)}" cy="${py(p.v)}" r="15" fill="transparent"/>
        <circle cx="${px(p.i)}" cy="${py(p.v)}" r="5.4" fill="#fff" stroke="${TONEC[t]}" stroke-width="2.8"/>
        <text class="tick b" x="${lx}" y="${ly}" text-anchor="${first?"start":(last?"end":"middle")}" ${HALO}>${n1(p.v)}</text></g>`;
    });
    const l=solid[solid.length-1];
    s += `<text class="tick b" x="${px(l.i)+15}" y="${py(l.v)+4}" fill="#0E2E56" ${HALO}>Cakupan aktif</text>`;
  }
  const filtered = rows.length!==DOSEN.length;
  if(filtered){
    const ip = YEARS.map((y,i)=>({i,v:avgOf(DOSEN,y)})).filter(p=>p.v!=null);
    s += `<path d="${ip.map((p,j)=>`${j?"L":"M"}${px(p.i)},${py(p.v)}`).join(" ")}" fill="none" stroke="#5E7288" stroke-width="1.9" stroke-dasharray="5 4"/>`;
    const l=ip[ip.length-1];
    if(l) s += `<text class="tick" x="${px(l.i)+15}" y="${py(l.v)+20}" fill="#5E7288" ${HALO}>Institusi</text>`;
  }
  s += `</svg>`;
  $("trend").innerHTML = s; tips($("trend"));

  const currentYear=YEARS.at(-1), previousYear=YEARS.at(-2);
  const a25=avgOf(rows,currentYear), a24=previousYear ? avgOf(rows,previousYear) : null;
  const delta = (a25!=null&&a24!=null)? a25-a24 : null;
  $("trendHint").innerHTML = delta==null ? "Rata-rata skor KPI untuk cakupan yang sedang dipilih."
    : `Rata-rata skor cakupan aktif ${delta>=0?"naik":"turun"} <b>${n1(Math.abs(delta))}</b> poin dari ${esc(previousYear)} ke ${esc(currentYear)}.`;
  $("trendLeg").innerHTML = `<span><i style="background:#0E2E56"></i>Rata-rata cakupan aktif</span>`
    + (filtered?`<span><i style="background:#5E7288"></i>Rata-rata seluruh institusi</span>`:"")
    + (!S.dosen?`<span><i style="background:#9CBFE3"></i>Rata-rata tiap program studi</span>`:"")
    + `<span><i style="background:#12764C"></i>Standar matriks skor 4</span>`;
}
const passFiltersExceptProdi = d => (!S.type||d.tipe===S.type)&&(!S.pend||d.jenjang===S.pend)&&(!S.jja||d.jabatan===S.jja)
  &&(!S.clu||d.cluster===S.clu)&&(!S.lab||d.labelMentor===S.lab);

/* ---------- 03 donut ---------- */
function renderDonut(){
  const rows=scope(), y=S.year;
  const counts = CAT.map(c=>({...c, n: rows.filter(d=>{const s=scoreOf(d,y); return s!=null && catOf(s).k===c.k;}).length}));
  const total = counts.reduce((a,c)=>a+c.n,0), noData = rows.length-total;
  const W=340,H=250,cx=W/2,cy=H/2+4,R=88,r=56;
  let s=`<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="Sebaran status produktivitas dosen">`;
  if(!total){
    $("donut").innerHTML = s+`<text class="tick" x="${cx}" y="${cy}" text-anchor="middle">Tidak ada dosen berskor pada cakupan ini</text></svg>`;
    $("donutLeg").innerHTML=""; $("donutHint").textContent=""; return;
  }
  let a0=-Math.PI/2;
  counts.forEach(c=>{
    if(!c.n) return;
    const sweep=c.n/total*Math.PI*2, a1=a0+Math.min(sweep, Math.PI*2-0.00001), big=sweep>Math.PI?1:0;
    const p=(a,rad)=>[cx+Math.cos(a)*rad, cy+Math.sin(a)*rad];
    const [x1,y1]=p(a0,R),[x2,y2]=p(a1,R),[x3,y3]=p(a1,r),[x4,y4]=p(a0,r);
    s+=`<path d="M${x1},${y1} A${R},${R} 0 ${big} 1 ${x2},${y2} L${x3},${y3} A${r},${r} 0 ${big} 0 ${x4},${y4} Z"
        fill="${c.c}" stroke="#fff" stroke-width="2"
        data-tip="<b>${c.k}</b><s>${c.desc}</s><s>${n0(c.n)} dosen · ${n1(c.n/total*100)}% dari yang berskor</s>"/>`;
    if(c.n/total>0.07){ const [lx,ly]=p((a0+a1)/2,(R+r)/2);
      s+=`<text x="${lx}" y="${ly+4}" text-anchor="middle" fill="#fff" font-size="12.5" font-weight="640" style="pointer-events:none">${c.n}</text>`; }
    a0=a1;
  });
  s+=`<text x="${cx}" y="${cy-4}" text-anchor="middle" font-size="26" font-weight="670" fill="#0C1A2B">${n0(total)}</text>`
   + `<text x="${cx}" y="${cy+15}" text-anchor="middle" font-size="11.5" fill="#6C7E93">dosen berskor ${y}</text></svg>`;
  $("donut").innerHTML=s; tips($("donut"));
  $("donutLeg").innerHTML = counts.map(c=>`<span><i style="background:${c.c}"></i>${c.k} <b class="num">${c.n}</b></span>`).join("")
    + (noData?`<span><i style="background:#D6DFEA"></i>Tanpa data <b class="num">${noData}</b></span>`:"");
  $("donutHint").innerHTML = `<b>${n1((counts[0].n+counts[1].n)/total*100)}%</b> dosen berada di kategori Good atau Excellent pada ${y}.`;
}

/* ---------- bar helpers ---------- */
function hbar(el, items, opt){
  opt=opt||{};
  if(!items.length){ el.innerHTML='<div class="empty">Belum ada data pada cakupan ini.</div>'; return; }
  const rowH=31, W=560, mL=opt.mL||112, mR=opt.mR||46, H=items.length*rowH+20;
  const max = Math.max(...items.map(i=>i.v), ...items.map(i=>i.ref||0), opt.min||0) || 1;
  const w = v => Math.max(0,(v/max)*(W-mL-mR));
  let s=`<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="${esc(opt.aria||'')}">`;
  items.forEach((it,i)=>{
    const y=i*rowH+10;
    s+=`<g data-tip="${it.tip}"><rect x="0" y="${y-8}" width="${W}" height="${rowH-3}" fill="transparent"/>
      <text class="tick b" x="${mL-9}" y="${y+13}" text-anchor="end">${esc(it.label)}</text>
      <rect x="${mL}" y="${y+2}" width="${W-mL-mR}" height="15" rx="4" fill="#F0F6FC"/>
      <rect x="${mL}" y="${y+2}" width="${w(it.v)}" height="15" rx="4" fill="${it.c}"/>
      ${it.ref!=null?`<line x1="${mL+w(it.ref)}" y1="${y-1}" x2="${mL+w(it.ref)}" y2="${y+20}" stroke="#0C1A2B" stroke-width="1.6" stroke-dasharray="3 2" opacity=".7"/>`:""}
      <text class="tick b" x="${W-6}" y="${y+14}" text-anchor="end">${it.vlab}</text></g>`;
  });
  el.innerHTML = s+`</svg>`; tips(el);
}
function sbar(el, items, keys, colors, aria){
  if(!items.length){ el.innerHTML='<div class="empty">Belum ada data pada cakupan ini.</div>'; return; }
  const rowH=30, W=560, mL=110, mR=44, H=items.length*rowH+20;
  const max = Math.max(...items.map(i=>keys.reduce((a,k)=>a+(i.v[k]||0),0)),1);
  const w = v => (v/max)*(W-mL-mR);
  let s=`<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="${esc(aria)}">`;
  items.forEach((it,i)=>{
    const y=i*rowH+10; let x=mL, tot=0;
    s+=`<text class="tick b" x="${mL-9}" y="${y+14}" text-anchor="end">${esc(it.label)}</text>`;
    keys.forEach(k=>{
      const v=it.v[k]||0; if(!v) return; tot+=v;
      const ww=w(v);
      s+=`<g data-tip="<b>${esc(it.full||it.label)}</b><s>${esc(k)}: ${n0(v)} dosen</s>">
        <rect x="${x}" y="${y+2}" width="${ww}" height="16" fill="${colors[k] || '#93A3B5'}" stroke="#fff" stroke-width="1"/>
        ${ww>17?`<text x="${x+ww/2}" y="${y+14}" text-anchor="middle" fill="#fff" font-size="11" font-weight="620" style="pointer-events:none">${v}</text>`:""}</g>`;
      x+=ww;
    });
    s+=`<text class="tick b" x="${W-6}" y="${y+14}" text-anchor="end">${n0(tot)}</text>`;
  });
  el.innerHTML = s+`</svg>`; tips(el);
}
function gbar(el, groups, keys, colors, aria){
  if(!groups.length){ el.innerHTML='<div class="empty">Belum ada laporan peran hibah.</div>'; return; }
  const W=640,H=215,mL=44,mR=16,mT=20,mB=32;
  const max = Math.max(...groups.flatMap(g=>keys.map(k=>g.v[k]||0)),1);
  const gw = (W-mL-mR)/groups.length, bw = Math.min(26,(gw-24)/keys.length);
  const py = v => mT + (1-v/max)*(H-mT-mB);
  let s=`<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="${esc(aria)}">`;
  for(let t=0;t<=4;t++){ const v=max*t/4, yy=py(v);
    s+=`<line class="gridline" x1="${mL}" y1="${yy}" x2="${W-mR}" y2="${yy}"/><text class="tick" x="${mL-8}" y="${yy+4}" text-anchor="end">${n0(v)}</text>`; }
  groups.forEach((g,i)=>{
    const x0 = mL + i*gw + (gw - bw*keys.length)/2;
    s+=`<text class="tick b" x="${mL+i*gw+gw/2}" y="${H-mB+19}" text-anchor="middle">${esc(g.label)}</text>`;
    keys.forEach((k,j)=>{
      const v=g.v[k]||0, x=x0+j*bw, hh=Math.max(1,(H-mB)-py(v));
      s+=`<g data-tip="<b>${esc(g.label)}</b><s>${esc(k)}: ${n0(v)} peran</s>">
        <rect x="${x}" y="${py(v)}" width="${bw-3}" height="${hh}" rx="3" fill="${colors[k]}"/>
        ${v?`<text class="tick b" x="${x+(bw-3)/2}" y="${py(v)-5}" text-anchor="middle" ${HALO}>${v}</text>`:""}</g>`;
    });
  });
  s+=`<line class="axis" x1="${mL}" y1="${H-mB}" x2="${W-mR}" y2="${H-mB}"/>`;
  el.innerHTML = s+`</svg>`; tips(el);
}

/* ---------- 04 prodi ---------- */
function renderProdi(){
  const y=S.year, base = DOSEN.filter(passFiltersExceptProdi);
  const keys=[...new Set(base.map(d=>d.prodi))];
  const instAvg = avgOf(base,y);
  const a = keys.map(k=>{ const Lp=base.filter(d=>d.prodi===k), v=avgOf(Lp,y);
      return {k,v,nn:Lp.filter(d=>scoreOf(d,y)!=null).length,tot:Lp.length}; })
    .filter(x=>x.v!=null).sort((x,z)=>z.v-x.v);
  hbar($("prodiKPI"), a.map(x=>({label:x.k, v:x.v, ref:instAvg, c: x.v>=instAvg?"#16406F":"#5B98D8", vlab:n1(x.v),
      tip:`<b>${esc(PRODI[x.k]||x.k)}</b><s>Rata-rata skor ${n1(x.v)} dari 6 (${n1(pct(x.v))}%)</s><s>${n0(x.nn)} dari ${n0(x.tot)} dosen punya skor ${y}</s>`})),
    {aria:"Peringkat prodi menurut rata-rata skor KPI"});
  $("pr1Hint").innerHTML = `Garis putus-putus adalah rata-rata institusi (<b>${n1(instAvg)}</b>) pada ${y}. Prodi navy berada di atas rata-rata.`;

  const instPer = base.reduce((s,d)=>s+d.totHibah,0)/(base.length||1);
  const b = keys.map(k=>{ const Lp=base.filter(d=>d.prodi===k);
      const h=Lp.reduce((s,d)=>s+d.totHibah,0), kt=Lp.reduce((s,d)=>s+d.totKetua,0);
      return {k,per:h/Lp.length,h,kt,tot:Lp.length}; }).sort((x,z)=>z.per-x.per);
  hbar($("prodiHibah"), b.map(x=>({label:x.k, v:x.per, ref:instPer, c: x.per>=instPer?"#12764C":"#C89A20", vlab:n1(x.per),
      tip:`<b>${esc(PRODI[x.k]||x.k)}</b><s>${n0(x.h)} keterlibatan dari ${n0(x.tot)} dosen · ${n1(x.per)} per dosen</s><s>${n0(x.kt)} peran ketua</s>`})),
    {aria:"Keterlibatan hibah per dosen menurut prodi"});
}

/* ---------- 05 scatter ---------- */
function renderScatter(){
  const y=S.year, rows=scope().filter(d=>scoreOf(d,y)!=null && d.ambang4num!=null);
  const W=620,H=380,mL=54,mR=18,mT=18,mB=48;
  const yMax=Math.max(4.6, ...rows.map(d=>d.ambang4num+0.6)), xS=STANDAR, yS=1.5;
  const px = v => mL + (v/MAXSCORE)*(W-mL-mR);
  const py = v => mT + (1-v/yMax)*(H-mT-mB);
  const QC = {"High Performer":"#12764C","Potential Contributor":"#2E77C4","Need Support":"#C89A20","Critical":"#A93226"};
  const quad = d => { const hi=scoreOf(d,y)>=xS, big=d.ambang4num>=yS;
    return hi ? (big?"High Performer":"Potential Contributor") : (big?"Critical":"Need Support"); };
  let s=`<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="Matriks risiko publikasi">`;
  s+=`<rect x="${px(xS)}" y="${mT}" width="${W-mR-px(xS)}" height="${py(yS)-mT}" fill="#12764C" opacity=".05"/>`
   + `<rect x="${px(xS)}" y="${py(yS)}" width="${W-mR-px(xS)}" height="${H-mB-py(yS)}" fill="#2E77C4" opacity=".05"/>`
   + `<rect x="${mL}" y="${mT}" width="${px(xS)-mL}" height="${py(yS)-mT}" fill="#A93226" opacity=".06"/>`
   + `<rect x="${mL}" y="${py(yS)}" width="${px(xS)-mL}" height="${H-mB-py(yS)}" fill="#C89A20" opacity=".06"/>`;
  [0,0.5,1,2,3,4,5,6].filter(g=>g<yMax).forEach(g=>{ s+=`<line class="gridline" x1="${mL}" y1="${py(g)}" x2="${W-mR}" y2="${py(g)}"/>`
    + `<text class="tick" x="${mL-8}" y="${py(g)+4}" text-anchor="end">${n2(g)}</text>`; });
  for(let g=0;g<=6;g++) s+=`<text class="tick" x="${px(g)}" y="${H-mB+18}" text-anchor="middle">${g}</text>`;
  s+=`<line class="axis" x1="${mL}" y1="${H-mB}" x2="${W-mR}" y2="${H-mB}"/><line class="axis" x1="${mL}" y1="${mT}" x2="${mL}" y2="${H-mB}"/>`
   + `<line x1="${px(xS)}" y1="${mT}" x2="${px(xS)}" y2="${H-mB}" stroke="#6C7E93" stroke-width="1.2" stroke-dasharray="4 4"/>`
   + `<line x1="${mL}" y1="${py(yS)}" x2="${W-mR}" y2="${py(yS)}" stroke="#6C7E93" stroke-width="1.2" stroke-dasharray="4 4"/>`
   + `<text class="alab" x="${(W+mL)/2}" y="${H-mB+38}" text-anchor="middle">Skor KPI ${y} (skala 0–6)</text>`
   + `<text class="alab" transform="translate(14,${(H-mB+mT)/2}) rotate(-90)" text-anchor="middle">Ambang bobot Skor 4 kolom matriks</text>`;
  const QH=`${HALO} font-weight="620" font-size="11"`;
  s+=`<text x="${W-mR-8}" y="${mT+13}" text-anchor="end" fill="#12764C" ${QH}>High Performer</text>`
   + `<text x="${mL+8}" y="${mT+13}" fill="#A93226" ${QH}>Critical</text>`
   + `<text x="${mL+8}" y="${H-mB-9}" fill="#B5790C" ${QH}>Need Support</text>`
   + `<text x="${W-mR-8}" y="${H-mB-9}" text-anchor="end" fill="#2E77C4" ${QH}>Potential Contributor</text>`;
  const buckets={}, counts={"High Performer":0,"Potential Contributor":0,"Need Support":0,"Critical":0};
  rows.forEach(d=>{ const k=`${scoreOf(d,y)}|${d.ambang4num}`; (buckets[k]=buckets[k]||[]).push(d); });
  Object.values(buckets).forEach(gp=>gp.forEach((d,i)=>{
    const q=quad(d); counts[q]++;
    const ang=i*2.399, rad=i?4.4*Math.sqrt(i):0;
    const cx=px(scoreOf(d,y))+Math.cos(ang)*rad, cy=py(d.ambang4num)+Math.sin(ang)*rad;
    const r=4+Math.min(7,Math.sqrt(d.totHibah)*1.8);
    s+=`<circle cx="${cx}" cy="${cy}" r="${r}" fill="${QC[q]}" fill-opacity=".6" stroke="${QC[q]}" stroke-width="1.1"
      data-tip="<b>${esc(shortName(d.nama))}</b><s>${esc(d.prodi)} · ${esc(d.kolom)} · Cluster ${esc(d.cluster)}</s><s>Skor KPI ${y}: ${n1(scoreOf(d,y))} · ${q}</s><s>Ambang Skor 4: ${n2(d.ambang4num)} · keterlibatan hibah: ${n0(d.totHibah)}</s>"/>`;
  }));
  $("scatter").innerHTML = s+`</svg>`; tips($("scatter"));
  const sinta = scope().filter(d=>scoreOf(d,y)!=null && d.ambang4num==null).length;
  $("scatLeg").innerHTML = Object.keys(QC).map(k=>`<span><i style="background:${QC[k]}"></i>${k} <b class="num">${counts[k]}</b></span>`).join("")
    + `<span style="color:#6C7E93">Ukuran titik = keterlibatan hibah</span>`;
  $("scatHint").innerHTML = `Batas kuadran: skor KPI <b>4</b> (standar matriks) dan ambang bobot Skor 4 sebesar <b>1,50</b>. Dosen di kiri-atas menanggung ambang tinggi dengan capaian rendah.`
    + (sinta?` ${n0(sinta)} dosen kolom TP tidak dapat dipetakan karena ambangnya kualitatif (SINTA).`:"");
}

/* ---------- 06 & 07 tabel ---------- */
function scoreCell(sv){
  if(sv==null) return `<span class="pill p-neu">Tanpa data</span>`;
  return `<div class="sc"><div class="track"><i style="width:${pct(sv)}%;background:${catColor(catOf(sv).k)}"></i></div><b class="num">${n1(sv)}</b></div>`;
}
function labPill(l){
  const cls = l==="MENTOR"?"p-ok":l==="KANDIDAT MENTOR"?"p-warn":l==="MENTEE"?"p-bad":"p-neu";
  return `<span class="pill ${cls}">${esc(l)}</span>`;
}
function renderTop(){
  const y=S.year;
  const rows = scope().map(d=>({d,s:scoreOf(d,y)})).filter(x=>x.s!=null)
    .sort((a,b)=> b.s-a.s || b.d.totHibah-a.d.totHibah || a.d.nama.localeCompare(b.d.nama,"id"));
  let rank=0, prev=null, seen=0;
  rows.forEach(x=>{ seen++; if(x.s!==prev){ rank=seen; prev=x.s; } x.rank=rank; });
  const list = S.topN ? rows.slice(0,S.topN) : rows;
  if(!list.length){
    $("topTbl").innerHTML=`<div class="empty">Tidak ada dosen dengan skor KPI ${y} pada cakupan ini. Longgarkan filter di bagian atas halaman.</div>`;
    $("topNote").innerHTML=""; return;
  }
  const ties = rows.filter(x=>x.rank===1).length;
  $("topNote").innerHTML = `Peringkat memakai RANK.EQ, sehingga skor yang sama berbagi nomor peringkat &mdash; saat ini <b class="num">${n0(ties)}</b> dosen berbagi peringkat 1 dengan skor ${n1(rows[0].s)}. Urutan di dalam peringkat yang sama ditentukan oleh keterlibatan hibah.`;
  $("topTbl").innerHTML = `<table class="tbl"><thead><tr><th class="c">#</th><th>Nama dosen</th><th>Prodi</th>
      <th>Kolom matriks</th><th style="min-width:130px">Skor KPI ${y}</th><th class="r">Hibah</th><th>Status</th><th>Label</th></tr></thead><tbody>`
    + list.map(x=>{ const c=catOf(x.s);
        const cls = c.k==="Excellent"?"p-ok":c.k==="Good"?"p-blue":c.k==="Moderate"?"p-warn":"p-bad";
        return `<tr><td class="c"><span class="rank${x.rank<=3?" top":""}">${x.rank}</span></td>
          <td class="nm" data-tip="<b>${esc(x.d.nama)}</b><s>${esc(x.d.kode)} · ${esc(x.d.tipe)}</s>">${esc(shortName(x.d.nama))}</td>
          <td data-tip="<b>${esc(PRODI[x.d.prodi]||x.d.prodi)}</b>"><span class="pill p-neu">${esc(x.d.prodi)}</span></td>
          <td style="color:var(--ink-2)">${esc(x.d.kolom)}</td>
          <td>${scoreCell(x.s)}</td>
          <td class="r num" data-tip="<s>${n0(x.d.totKetua)} ketua · ${n0(x.d.totAnggota)} anggota</s>">${n0(x.d.totHibah)}</td>
          <td><span class="pill ${cls}">${c.k}</span></td>
          <td>${labPill(x.d.labelMentor)}</td></tr>`;
      }).join("") + `</tbody></table>`;
  tips($("topTbl"));
}
function renderNeed(){
  const y=S.year;
  const rows = scope().map(d=>({d,s:scoreOf(d,y)})).filter(x=>x.s!=null && pct(x.s)<50)
    .sort((a,b)=> a.s-b.s || (b.d.ambang4num||0)-(a.d.ambang4num||0));
  const all = scope().filter(d=>scoreOf(d,y)!=null).length;
  $("needHint").innerHTML = rows.length
    ? `<b class="num">${n0(rows.length)}</b> dari ${n0(all)} dosen berskor di bawah 50% (skor KPI &lt; 3) pada ${y}. Gap adalah selisih poin skor menuju standar matriks 4.`
    : `Tidak ada dosen di bawah 50% pada cakupan ini.`;
  if(!rows.length){ $("needTbl").innerHTML=`<div class="empty">${all ? 'Seluruh dosen berskor pada cakupan ini berada di atas ambang 50%.' : 'Belum ada skor KPI pada cakupan ini.'}</div>`; return; }
  $("needTbl").innerHTML = `<table class="tbl"><thead><tr><th>Nama dosen</th><th>Prodi</th>
      <th style="min-width:118px">Skor ${y}</th><th class="r">Gap</th><th>Pendampingan</th></tr></thead><tbody>`
    + rows.map(x=>`<tr>
        <td class="nm" data-tip="<b>${esc(x.d.nama)}</b><s>${esc(x.d.kolom)} · ambang Skor 4: ${x.d.ambang4num==null?"kualitatif (SINTA)":n2(x.d.ambang4num)}</s><s>${esc(x.d.statusKeterlibatan)}</s>">${esc(shortName(x.d.nama))}</td>
        <td><span class="pill p-neu">${esc(x.d.prodi)}</span></td>
        <td>${scoreCell(x.s)}</td>
        <td class="r"><span class="pill p-bad">+${n0(STANDAR-x.s)}</span></td>
        <td>${labPill(x.d.labelMentor)}</td></tr>`).join("") + `</tbody></table>`;
  tips($("needTbl"));
}

/* ---------- 08 kapasitas ---------- */
function renderKapasitas(){
  const base = scope();
  const keys=[...new Set(base.map(d=>d.prodi))];
  const LK=[...new Set(["MENTOR","KANDIDAT MENTOR","MENTEE","Tidak tercatat", ...base.map(d=>d.labelMentor)])];
  const HK=["Pernah menjadi ketua","Hanya sebagai anggota","Belum pernah terlibat","Tidak tercatat"];
  const mk = keys.map(k=>{ const Lp=base.filter(d=>d.prodi===k), v={};
      LK.forEach(l=>v[l]=Lp.filter(d=>d.labelMentor===l).length);
      return {label:k, full:PRODI[k]||k, v, mentor:v["MENTOR"]}; })
    .sort((a,b)=>b.mentor-a.mentor || (b.v.MENTEE+b.v.MENTOR)-(a.v.MENTEE+a.v.MENTOR));
  sbar($("mentorBar"), mk, LK, LABC, "Sebaran label mentor per prodi");
  $("mentorLeg").innerHTML = LK.filter(l=>base.some(d=>d.labelMentor===l))
    .map(l=>`<span><i style="background:${LABC[l] || '#93A3B5'}"></i>${esc(l)} <b class="num">${base.filter(d=>d.labelMentor===l).length}</b></span>`).join("");
  const nM=base.filter(d=>d.labelMentor==="MENTOR").length, nE=base.filter(d=>d.labelMentor==="MENTEE").length;
  $("mentorHint").innerHTML = nM ? `Rasio mentee terhadap mentor pada cakupan ini <b>${n1(nE/nM)} : 1</b>. Kandidat mentor adalah cadangan bila rasio terlalu berat.`
    : `Tidak ada dosen berlabel mentor pada cakupan ini.`;

  const hk = keys.map(k=>{ const Lp=base.filter(d=>d.prodi===k), v={};
      HK.forEach(l=>v[l]=Lp.filter(d=>d.statusKeterlibatan===l).length);
      return {label:k, full:PRODI[k]||k, v, lead:v["Pernah menjadi ketua"]}; })
    .sort((a,b)=>b.lead-a.lead);
  sbar($("hibahBar"), hk, HK, HIBC, "Status keterlibatan hibah per prodi");
  $("hibahLeg").innerHTML = HK.filter(l=>base.some(d=>d.statusKeterlibatan===l))
    .map(l=>`<span><i style="background:${HIBC[l]}"></i>${l} <b class="num">${base.filter(d=>d.statusKeterlibatan===l).length}</b></span>`).join("");
  const belum=base.filter(d=>d.statusKeterlibatan==="Tidak tercatat").length;
  $("hibahHint").innerHTML = `<b class="num">${n0(belum)}</b> dosen belum memiliki peran pada laporan hibah yang tersedia. Riwayat yang belum tercatat tidak dianggap belum pernah terlibat.`;

  const rows=scope();
  const groups = HYEARS.map((yr,i)=>({label:yr, v:{
      "Ketua": rows.reduce((a,d)=>a+(d.ketua[i]||0),0),
      "Anggota": rows.reduce((a,d)=>a+(d.anggota[i]||0),0)}}));
  gbar($("peranBar"), groups, ["Ketua","Anggota"], {"Ketua":"#16406F","Anggota":"#5B98D8"}, "Peran hibah per tahun");
  $("peranLeg").innerHTML = `<span><i style="background:#16406F"></i>Ketua <b class="num">${n0(groups.reduce((a,g)=>a+g.v.Ketua,0))}</b></span>`
    + `<span><i style="background:#5B98D8"></i>Anggota <b class="num">${n0(groups.reduce((a,g)=>a+g.v.Anggota,0))}</b></span>`
    + `<span style="color:#6C7E93">Tahun laporan: ${esc(yearRange(HYEARS))}</span>`;
}

/* ---------- 09 matriks & cluster ---------- */
function renderMatriks(){
  $("matriksTbl").innerHTML = `<table class="tbl"><thead><tr><th rowspan="2">Kolom matriks</th>
      <th colspan="4" style="text-align:center;border-left:1px solid var(--line)">Functional</th>
      <th colspan="4" style="text-align:center;border-left:1px solid var(--line)">Professional</th></tr>
    <tr><th class="r" style="border-left:1px solid var(--line)">Skor 3</th><th class="r">Skor 4</th><th>Skor 5</th><th class="r">Skor 6</th>
        <th class="r" style="border-left:1px solid var(--line)">Skor 3</th><th class="r">Skor 4</th><th>Skor 5</th><th class="r">Skor 6</th></tr></thead><tbody>`
    + D.matriks.map(m=>{
        const cell=v=>v==null?`<span style="color:var(--ink-4)">—</span>`:n2(v);
        const rule=v=>v==null?`<span style="color:var(--ink-4)">—</span>`:`<span style="font-size:11.5px;color:var(--ink-2)">${esc(v)}</span>`;
        return `<tr><td class="nm">${esc(m.kolom)}</td>
          <td class="r num" style="border-left:1px solid var(--line-2)">${cell(m.f3)}</td><td class="r num">${cell(m.f4)}</td><td>${rule(m.f5)}</td><td class="r num">${cell(m.f6)}</td>
          <td class="r num" style="border-left:1px solid var(--line-2)">${cell(m.p3)}</td><td class="r num">${cell(m.p4)}</td><td>${rule(m.p5)}</td><td class="r num">${cell(m.p6)}</td></tr>`;
      }).join("") + `</tbody></table>`;

  const rows = scope();
  const order=["AA S2","L S2","AA S3 / TP S3 / LK S2","L S3","L S3 / LK S3","GB","TP S1 / TP S2","Tidak terpetakan"];
  const items = order.filter(k=>rows.some(d=>d.kolom===k)).map(k=>{
      const Lp = rows.filter(d=>d.kolom===k);
      const amb = Lp.map(d=>d.ambang4num).filter(v=>v!=null);
      // warna mengikuti ambang terberat di kolom itu (jalur Functional selalu paling berat)
      const hi = amb.length ? Math.max(...amb) : null;
      const lo = amb.length ? Math.min(...amb) : null;
      const rng = hi==null ? "kualitatif (SINTA)" : (hi===lo ? n2(hi) : `${n2(lo)} – ${n2(hi)}`);
      return {label:k, v:Lp.length, c: hi==null?"#93A3B5":(hi>=2?"#A93226":hi>=1?"#C89A20":"#12764C"),
        vlab:n0(Lp.length),
        tip:`<b>${esc(k)}</b><s>${n0(Lp.length)} dosen</s><s>Ambang bobot Skor 4: ${rng}</s>`};
    }).sort((a,b)=>b.v-a.v);
  hbar($("kolomBar"), items, {aria:"Jumlah dosen per kolom matriks", mL:172, mR:52});
  const berat = rows.filter(d=>d.ambang4num!=null && d.ambang4num>=2).length;
  $("kolomHint").innerHTML = `Sebaran dosen per kolom pada cakupan aktif. <b class="num">${n0(berat)}</b> dosen berada di kolom berambang berat (bobot ≥ 2,00), dan warna batang mengikuti berat ambang itu.`;

  $("cluBox").innerHTML = ["A","B","C"].map(c=>{
    const n = scope().filter(d=>d.cluster===c).length;
    const info = D.cluster[c];
    return `<div class="clu"><h4><span class="badge">${c}</span> Cluster ${c}
        <span class="pill p-blue num" style="margin-left:auto">${n0(n)} dosen</span></h4>
      <ul>${info.kriteria.map(x=>`<li>${esc(x)}</li>`).join("")}</ul>
      <div style="display:flex;gap:6px;flex-wrap:wrap">${info.hibah.map(h=>`<span class="pill p-neu">${esc(h)}</span>`).join("")}</div></div>`;
  }).join("") + (scope().some(d=>d.cluster==="Tidak tercatat")
      ? `<p class="note">${n0(scope().filter(d=>d.cluster==="Tidak tercatat").length)} dosen belum memiliki penetapan cluster pada tahun ${esc(S.year || 'terpilih')}.</p>` : "");
}

/* ---------- 10 topik & SDG ---------- */
function renderSDG(){
  const rows=scope();
  const cnt={};
  rows.forEach(d=>d.sdg.forEach(s=>cnt[s]=(cnt[s]||0)+1));
  const items = Object.entries(cnt).sort((a,b)=>b[1]-a[1]||a[0].localeCompare(b[0]))
    .map(([k,v])=>({label:k, v, c:"#2E77C4", vlab:n0(v),
      tip:`<b>${esc(k)}</b><s>${n0(v)} dosen membidik SDG ini</s>`}));
  if(!items.length){ $("sdgBar").innerHTML=`<div class="empty">Belum ada SDG tercatat pada cakupan ini.</div>`; $("sdgHint").textContent=""; return; }
  items[0].c="#16406F";
  hbar($("sdgBar"), items, {aria:"Frekuensi SDG yang dibidik", mL:66});
  const withTopic = rows.filter(d=>d.topik).length;
  $("sdgHint").innerHTML = `<b class="num">${n0(withTopic)}</b> dari ${n0(rows.length)} dosen sudah merumuskan topik prioritas 2027.`;
}
function renderTopik(){
  const rows=scope().filter(d=>d.topik);
  const q=S.q;
  const hit = !q ? rows : rows.filter(d=>
    (d.nama+" "+d.prodi+" "+(d.topik||"")+" "+d.sdg.join(" ")).toLowerCase().includes(q));
  $("topikHint").innerHTML = `Menampilkan <b class="num">${n0(hit.length)}</b> rumusan topik${q?` yang memuat “${esc(S.q)}”`:""}.`;
  if(!hit.length){ $("topikTbl").innerHTML=`<div class="empty">Tidak ada topik yang cocok. Coba kata kunci lain atau longgarkan filter.</div>`; return; }
  $("topikTbl").innerHTML = `<table class="tbl"><thead><tr><th style="min-width:150px">Dosen</th><th>SDG</th><th>Topik prioritas 2027</th></tr></thead><tbody>`
    + hit.sort((a,b)=>a.prodi.localeCompare(b.prodi)||a.nama.localeCompare(b.nama,"id")).map(d=>`<tr>
        <td><div class="nm">${esc(shortName(d.nama))}</div><span class="pill p-neu" style="margin-top:3px">${esc(d.prodi)}</span></td>
        <td>${d.sdg.map(s=>`<span class="pill p-blue" style="margin:1px 0">${esc(s)}</span>`).join(" ")||`<span style="color:var(--ink-4)">—</span>`}</td>
        <td class="topik">${esc(d.topik)}</td></tr>`).join("") + `</tbody></table>`;
}

/* ---------- footnote ---------- */
function renderFoot(){
  const snapshot=source.snapshots[S.year];
  const snapshotNote=snapshot ? `Snapshot ${snapshot.month}/${snapshot.year}, Quarter ${snapshot.period}${snapshot.filename ? ' · '+esc(snapshot.filename) : ''}.` : 'Belum ada snapshot publikasi.';
  const scoreNote=snapshot?.has_kpi ? 'Score KPI mengikuti Score KPI RTTO pada workbook; nol yang tercatat tetap dihitung. Bobot publikasi menggunakan nilai tertinggi Rectorate–RTTO per kategori.' : 'Skor KPI dihitung dengan matriks sistem, bobot penyesuaian publikasi, dan profil dosen yang tersedia.';
  $("footnote").innerHTML = `<b>Sumber data: database RCDC Malang dan laporan Excel.</b> ${snapshotNote} ${scoreNote}
    Skor memakai skala 0–6; capaian adalah skor dibagi 6 dan standar matriks adalah skor 4. Skor kosong tidak dianggap nol.
    Hibah mengikuti peran ketua/anggota yang tercatat; mentor, cluster, serta topik 2027 mengikuti penetapan dalam sistem.
    Ringkasan pedoman matriks dan cluster mengikuti contoh dashboard. Kolom TP S1/S2 bersifat kualitatif; profil tanpa ambang numerik tidak diplot pada risk matrix.`;
  if(snapshot?.warnings?.length) $("footnote").innerHTML += `<p>${snapshot.warnings.map(esc).join('<br>')}</p>`;
}

/* ---------- render ---------- */
function render(){
  tipEl.style.opacity=0;
  renderKPI(); renderTrend(); renderDonut(); renderProdi(); renderScatter();
  renderTop(); renderNeed(); renderKapasitas(); renderMatriks(); renderSDG(); renderTopik();
  renderFoot();
}
buildFilters(); render();

})();
