const departments = [
  { id: "ebc-fm", dept: "EBC", group: "FM", score: "6.00", fmCount: "Jumlah FM: 10 FM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 },
  { id: "cs-fm", dept: "CS", group: "FM", score: "6.00", fmCount: "Jumlah FM: 10 FM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 },
  { id: "dkv-fm", dept: "DKV", group: "FM", score: "6.00", fmCount: "Jumlah FM: 10 FM; 1 AFM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 },
  { id: "ebc-mhs", dept: "EBC", group: "MHS", score: "6.00", fmCount: "Jumlah FM: 10 FM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 },
  { id: "cs-mhs", dept: "CS", group: "MHS", score: "6.00", fmCount: "Jumlah FM: 10 FM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 },
  { id: "dkv-mhs", dept: "DKV", group: "MHS", score: "6.00", fmCount: "Jumlah FM: 10 FM; 1 AFM", target: 14, actual: 17, fmPercent: 50, papers: 12.5, conferences: 12.5, journals: 12.5, bookChapter: 12.5, fullBook: 12.5 }
];

const grid = document.getElementById("departmentGrid");
const yearFilter = document.getElementById("yearFilter");
const yearMultiFilter=document.getElementById("yearMultiFilter");
const yearChoices=Array.from(document.querySelectorAll(".year-choice"));
let selectedYears=new Set(["2026"]);
function selectedYearList() { return [...selectedYears].sort(); }
function updateYearMultiLabel() {
  const years=selectedYearList();
  document.getElementById("yearMultiLabel").textContent=years.length===4 ? "Semua Tahun (2023–2026)" : years.length===1 ? `Tahun ${years[0]}` : `Tahun ${years.join(", ")}`;
  yearChoices.forEach(input=>{input.checked=selectedYears.has(input.value);});
}
function updateYearSelection(years) {
  selectedYears=new Set(years.length ? years : ["2026"]);
  // Editor uses a single, explicit year. Preserve the selected edit year when possible.
  if(!selectedYears.has(yearFilter.value)) yearFilter.value=selectedYearList().at(-1);
  selectedCardId="";
  updateYearMultiLabel();
  renderCards();
}

const indicatorFilter = document.getElementById("indicatorFilter");
const quarterFilter = document.getElementById("quarterFilter");
const modal = document.getElementById("detailModal");
const modalTitle = document.getElementById("modalTitle");
const modalBody = document.getElementById("modalBody");

function getScoreColorClass(value) {
  // Nilai desimal mengikuti rentang: [1,3) merah; [3,4) kuning; [4,6] hijau.
  const raw = String(value ?? "").trim();
  if (!raw) return "";
  const score = Number(raw.replace(",", "."));
  if (!Number.isFinite(score)) return "";
  if (score >= 1 && score < 3) return "score-red";
  if (score >= 3 && score < 4) return "score-yellow";
  if (score >= 4 && score <= 6) return "score-green";
  return "";
}

function computeChartGap(targetValue, actualValue) {
  // GAP = Realisasi - Target. Positif: melebihi target; negatif: masih kurang.
  function toNumber(value) {
    if (value === null || value === undefined || String(value).trim() === "") return null;
    const number = Number(String(value).trim().replace(",", "."));
    return Number.isFinite(number) ? number : null;
  }
  const target = toNumber(targetValue);
  const actual = toNumber(actualValue);
  if (target === null || actual === null) {
    return { value: "—", css: "gap-unknown", status: "Data belum lengkap", explanation: "Target atau realisasi belum tersedia" };
  }
  const gap = Math.round((actual - target) * 100) / 100;
  const formatted = new Intl.NumberFormat("id-ID", { maximumFractionDigits: 2 }).format(Math.abs(gap));
  if (gap > 0) return { value: "+" + formatted, css: "gap-surplus", status: "", explanation: "Realisasi lebih tinggi daripada target sebesar " + formatted };
  if (gap < 0) return { value: "−" + formatted, css: "gap-shortfall", status: "Belum tercapai", explanation: "Kekurangan " + formatted + " dari target" };
  return { value: "0", css: "gap-met", status: "Sesuai target", explanation: "Target tercapai tepat" };
}

function makeCard(item, chartYear=yearFilter.value) {
  const gap = computeChartGap(item.target, item.actual);
  const max = Math.max(item.target, item.actual, 1);
  const targetHeight = safeNumber(item.target) === 0 ? 0 : Math.max(12, item.target / max * 134);
  const actualHeight = safeNumber(item.actual) === 0 ? 0 : Math.max(12, item.actual / max * 134);
  const card = document.createElement("article");
  card.className = "dept-card";
  card.dataset.dept = item.dept;
  card.dataset.indicator = item.group === "FM" ? "fm" : "publication";
  card.innerHTML = `
    <span class="chart-quarter-badge" title="Quartal grafik: ${escapeHtml(normalizeChartQuarter(item.quarter) || "Belum ditentukan")}">${escapeHtml(normalizeChartQuarter(item.quarter) || "Q—")}</span>
    <div class="dept-title"><span>${escapeHtml(item.displayName || item.dept)}<span class="chart-year-label">${escapeHtml(chartYear)}</span></span></div>
    ${adminMode ? `<div class="card-manage-actions">
      <button type="button" data-card-action="rename" title="Ganti nama grafik ini">✎ Ganti Nama</button>
      <button type="button" data-card-action="edit" title="Edit seluruh informasi grafik">⚙ Edit Data</button>
      <button type="button" data-card-action="delete" class="delete" title="Hapus grafik ini">✕ Hapus</button>
    </div>` : ""}
    <div class="score-row"><span class="score ${getScoreColorClass(item.score)}">Score ${escapeHtml(item.score)}</span></div>
    <div class="chart-area" aria-label="Perbandingan target, realisasi, GAP, dan quartal">
      <div class="chart-gap ${gap.css}" title="GAP = Realisasi − Target. ${escapeHtml(gap.explanation)}" aria-label="GAP ${escapeHtml(gap.value)}${gap.status ? `: ${escapeHtml(gap.status)}` : ""}">
        <span class="chart-gap-label">GAP</span>
        <span class="chart-gap-value">${escapeHtml(gap.value)}</span>
        ${gap.status ? `<span class="chart-gap-status">${escapeHtml(gap.status)}</span>` : ""}
      </div>
      <div class="bar-group"><span class="bar-value">${item.target}</span><div class="bar" style="height:${targetHeight}px"></div><span class="bar-label">Target</span></div>
      <div class="bar-group"><span class="bar-value">${item.actual}</span><div class="bar actual" style="height:${actualHeight}px"></div><span class="bar-label">Realisasi</span></div>
    </div>
    <div class="details">
      <div class="details-top"><span class="fm-count">${escapeHtml(item.fmCount)}</span><button class="detail-btn" type="button">Detail</button></div>
      <div class="detail-lines">
        <span class="highlight">% FM Have Scopus : ${item.fmPercent} % (${item.fmScopusCount??""} of ${item.fmTotal??""})</span>
        <span>Paper Scopus : (${item.papers} Bobot) atau (${item.paperTitles??""} Judul)</span>
        <span>Jurnal Scopus : (${item.journals} Bobot) atau (${item.journalTitles??""} Judul)</span>
        <span>Konferensi Scopus : (${item.conferences} Bobot) atau (${item.conferenceTitles??""} Judul)</span>
        <span>Book Chapter : (${item.bookChapter} Bobot) atau (${item.chapterTitles??""} Judul)</span>
        <span>Full Book : (${item.fullBook} Bobot) atau (${item.bookTitles??""} Judul)</span>
      </div>
    </div>`;
  card.querySelector(".detail-btn").addEventListener("click", () => {yearFilter.value=chartYear; showDetails(item);});
  if (adminMode) {
    card.querySelector('[data-card-action="rename"]').addEventListener("click", () => {yearFilter.value=chartYear; showCardRename(card, item);});
    card.querySelector('[data-card-action="edit"]').addEventListener("click", () => {
      yearFilter.value=chartYear;
      selectedCardId = item.id;
      renderAdmin();
      document.getElementById("cardEditor").scrollIntoView({behavior:"smooth",block:"center"});
      const nameInput=document.querySelector('#cardEditor [data-field="displayName"]');
      if(nameInput) nameInput.focus();
    });
    card.querySelector('[data-card-action="delete"]').addEventListener("click", () => {yearFilter.value=chartYear; removeCardById(item.id);});
  }
  return card;
}


function showCardRename(card, item) {
  const existing = card.querySelector(".card-rename-form");
  if (existing) { existing.querySelector("input").focus(); return; }
  const form = document.createElement("form");
  form.className = "card-rename-form";
  form.innerHTML = `<label><input type="text" maxlength="100" required aria-label="Nama baru grafik"
    value="${escapeHtml(item.displayName || item.dept)}" placeholder="Nama baru grafik"></label>
    <button type="submit">Simpan Nama</button>
    <button type="button" class="cancel">Batal</button>`;
  card.querySelector(".card-manage-actions").insertAdjacentElement("afterend",form);
  form.querySelector("input").focus();
  form.querySelector("input").select();
  form.querySelector(".cancel").addEventListener("click",()=>form.remove());
  form.addEventListener("submit",event=>{
    event.preventDefault();
    const newName=form.querySelector("input").value.trim();
    if(!newName) return;
    const data=currentDataset();
    const original=data?.cards.find(c=>c.id===item.id);
    if(!original) return;
    original.displayName=newName;
    persist();
    renderCards();
  });
}
function removeCardById(id) {
  const data=currentDataset();
  const card=data?.cards.find(c=>c.id===id);
  if(!card) return;
  const label=card.displayName || card.dept;
  if(!confirm(`Hapus grafik "${label}" dari dashboard?\\n\\nData dosen dan publikasi tidak akan ikut terhapus.`)) return;
  data.cards=data.cards.filter(c=>c.id!==id);
  if(selectedCardId===id) selectedCardId="";
  persist();
  renderCards();
}
// Ringkasan quartal memakai nilai per-quartal yang tersimpan; jika kosong,
// nilai FM/MHS dihitung dari grafik berlabel Q1–Q4, bukan dari angka tahunan.
function quarterSummaryForDataset(data, quarter) {
  if(!data) return Array(7).fill(null);
  const result=Array(7).fill(null);
  const tagged=(data.cards||[]).filter(c=>normalizeChartQuarter(c.quarter)===quarter);
  function sumGroup(group,key) {
    const matches=tagged.filter(c=>String(c.group||"FM").toUpperCase()===group);
    const numbers=matches.map(c=>c[key]).filter(v=>v!==null&&v!==undefined&&String(v).trim()!=="")
      .map(v=>Number(String(v).replace(",","."))).filter(Number.isFinite);
    return numbers.length ? String(Number(numbers.reduce((a,b)=>a+b,0).toFixed(3))) : null;
  }
  result[2]=sumGroup("FM","target");
  result[3]=sumGroup("FM","actual");
  result[4]=sumGroup("MHS","target");
  result[5]=sumGroup("MHS","actual");
  const fmCards=tagged.filter(c=>String(c.group||"FM").toUpperCase()==="FM");
  const valid=fmCards.filter(c=>c.fmScopusCount!==undefined && c.fmTotal!==undefined &&
    String(c.fmScopusCount).trim()!=="" && String(c.fmTotal).trim()!=="" &&
    Number.isFinite(Number(c.fmScopusCount)) && Number.isFinite(Number(c.fmTotal)) && Number(c.fmTotal)>0);
  if(valid.length && valid.length===fmCards.length) {
    const numerator=valid.reduce((a,c)=>a+Number(c.fmScopusCount),0);
    const denominator=valid.reduce((a,c)=>a+Number(c.fmTotal),0);
    if(denominator>0) result[6]=String(Number((numerator/denominator*100).toFixed(2)))+" %";
  }
  const overrides=data.quarterSummaries?.[quarter];
  if(Array.isArray(overrides)) overrides.slice(0,7).forEach((value,i)=>{
    if(value!==null && value!==undefined && String(value).trim()!=="") result[i]=String(value).trim();
  });
  return result;
}
function renderSummary(selectedDatasets,years) {
  const quarter=quarterFilter.value;
  const isQuarter=quarter!=="all";
  const selectedValues=selectedDatasets.map(({data})=>isQuarter ? quarterSummaryForDataset(data,quarter) : data?.summary||null);
  summaryIds.forEach((id,i)=>{
    const values=selectedValues.map(v=>v?.[i]).filter(v=>v!==null&&v!==undefined&&String(v).trim()!=="");
    let output="—";
    if(years.length===1) output=values.length?String(values[0]):"—";
    else if(i!==6 && values.length) {
      // Gabungkan hanya nilai numerik yang benar-benar tersedia.
      const nums=values.map(v=>Number(String(v).replace(/\s/g,"").replace(/,/g,".")));
      if(nums.every(Number.isFinite)) output=String(Number(nums.reduce((a,b)=>a+b,0).toFixed(3)));
    } else if(i===6 && isQuarter) {
      // Persentase multi-tahun dihitung dari pembilang/penyebut FM, bukan menjumlah persentase.
      const allCards=selectedDatasets.flatMap(({data})=>(data?.cards||[])
        .filter(c=>normalizeChartQuarter(c.quarter)===quarter && String(c.group||"FM").toUpperCase()==="FM"));
      const valid=allCards.filter(c=>c.fmScopusCount!==undefined&&c.fmTotal!==undefined&&
        String(c.fmScopusCount).trim()!==""&&String(c.fmTotal).trim()!==""&&
        Number.isFinite(Number(c.fmScopusCount))&&Number.isFinite(Number(c.fmTotal))&&Number(c.fmTotal)>0);
      if(valid.length && valid.length===allCards.length) {
        const num=valid.reduce((sum,c)=>sum+Number(c.fmScopusCount),0);
        const den=valid.reduce((sum,c)=>sum+Number(c.fmTotal),0);
        if(den>0) output=String(Number((100*num/den).toFixed(2)))+" %";
      }
    }
    document.getElementById(id).textContent=output;
  });
  const period=isQuarter?`Target ${quarter}`:"Target Q4";
  document.getElementById("fmTargetPeriod").textContent=period;
  document.getElementById("studentTargetPeriod").textContent=period;
  document.getElementById("milestoneTargetPeriod").textContent=isQuarter?`Target ${quarter} · ${years.join(", ")}`:`Target ${years.join(", ")}`;
}
function renderCards() {
  grid.innerHTML = "";
  const years=selectedYearList();
  const activeData=currentDataset();
  const selectedDatasets=years.map(year=>({year,data:appData.years?.[year]?.[dataKey()]||null}));
  renderSummary(selectedDatasets,years);
  const titles = visibleSummaryTitles(activeData);
  for(let i=0;i<4;i++) document.getElementById("summaryTitle"+(i+1)).textContent = titles[i];
  document.getElementById("activePublicationLabel").textContent = "— " + indicatorFilter.options[indicatorFilter.selectedIndex].text;
  document.getElementById("updateDate").value=appData.updated||"";
  let count=0;
  selectedDatasets.forEach(({year,data})=>{
    visiblePublicationCards(data).forEach(item=>{grid.appendChild(makeCard(item,year));count++;});
  });
  const empty=document.getElementById("emptyState");
  const missingYears=selectedDatasets.filter(({data})=>!data).map(({year})=>year);
  empty.textContent=count===0
    ? `Belum ada grafik untuk ${indicatorFilter.options[indicatorFilter.selectedIndex].text} pada tahun ${years.join(", ")}${quarterFilter.value!=="all" ? " / "+quarterFilter.value : ""}. Gunakan Kelola Data untuk menambahkan grafik pada tahun yang diinginkan.`
    : "";
  empty.style.display=count?"none":"block";
  let note=document.getElementById("multiYearCaption");
  if(!note) {note=document.createElement("div");note.id="multiYearCaption";note.className="multi-year-caption";grid.parentNode.insertBefore(note,grid);}
  note.textContent=years.length>1
    ? `Menampilkan ${count} grafik dari ${years.length} tahun (${years.join(", ")}). ${quarterFilter.value==="all" ? "Ringkasan target/realisasi dijumlahkan dari tahun yang tersedia; persentase tidak digabung." : "Ringkasan mengikuti "+quarterFilter.value+"; angka yang belum tersedia ditampilkan sebagai —."}${missingYears.length?" Belum ada dataset: "+missingYears.join(", ")+".":""}`
    : "";
  note.style.display=years.length>1?"block":"none";
  if(adminMode) renderAdmin();
}

// Data 20 dosen EBC ditranskripsi dari gambar yang diberikan pengguna.
// Kolom angka disimpan sebagai string agar tanda desimal koma tetap seperti sumber.
const ebcFaculty = [
  ["D5868","Agung Purnomo, S.P., MBA","BC","L300","Functional Faculty(A)","0,00","9,25","0","4"],
  ["D6552","Audio Valentino Himawan Mahrendra, S.AB., M.AB","BC","AA150","Professional Faculty(A)","0,00","3,58","0","4"],
  ["D5847","Dr. Kukuh Lukiyanto, S.T., M.M., M.T.","BC","LK400","Functional Faculty(C)","0,00","6,25","1","6"],
  ["D6259","Dr. Meilinda Trisilia, S.Si., M.Si.","BC","L200","Professional Faculty(A)","0,00","3,00","0","4"],
  ["D6358","Dr. Murniati, S.E., M.Si.","BC","L200","Functional Faculty(A)","1,00","4,25","0","4"],
  ["D6581","Dr. R. Aditya Kristamtomo Putra, S.T., M.M","BC","L200","Functional Faculty(A)","0,50","0,87","1","2"],
  ["D2693","Dr. Robertus Tang Herman, S.E., M.M.","BC","LK400","Functional Faculty(A)","0,00","1,00","0","2"],
  ["D6373","Dr. Shahnaz Ubud, S.T., M.MT","BC","L200","Functional Faculty(A)","0,00","3,50","0","3"],
  ["D5835","Dr. Yoseph Benny Kusuma, S.M., M.SM.","BC","AA150","Functional Faculty(C)","0,00","1,00","0","3"],
  ["D7213","Elysabet Christy Diandra Selano, S.Ak., M.A.","BC","TP","Functional Faculty(A)","2,00","0,00","0","4"],
  ["D6161","Etsa Astridya Setiyati, S.E., M.Comm.","BC","L200","Functional Faculty(A)","0,00","1,20","0","4"],
  ["D6258","Febby Candra Pratama, S.E., M.M.","BC","AA150","Functional Faculty(A)","0,00","5,25","1","6"],
  ["D7056","Hany Azza Umama, S.E., M.M.","BC","AA150","Functional Faculty(A)","0,00","1,00","1","6"],
  ["D7058","Harris Prasetya Rahmandika, S.M., M.M.","BC","TP","Professional Faculty(A)","1,00","0,00","0","1"],
  ["D6605","Helena Hanindya Kartika Putri, S.S., MBA","BC","AA150","Functional Faculty(A)","0,00","0,00","0","0"],
  ["D6166","Hera Rachmahani, S.M., M.S.M.","BC","AA150","Functional Faculty(A)","0,00","0,00","0","0"],
  ["D6966","Krismi Budi Sienatra, S.E., M.M.","BC","L300","Functional Faculty(A)","0,00","2,20","1","4"],
  ["D6284","Mardhatilah Shanti, S.E., M.M.","BC","L200","Functional Faculty(A)","0,00","3,00","0","4"],
  ["D6587","Mufida Sekardhani, S.E., MBA","BC","AA150","Functional Faculty(A)","0,00","1,08","0","4"],
  ["D5390","Muhammad Idham Sofyan, S.E., M.M.","BC","AA150","Functional Faculty(A)","0,00","0,00","0","0"]
];
const facultyHeaders = ["Kode Dosen","Nama Dosen","Jurusan Binaan","JJA","Faculty Type","Non Scopus","Scopus","Jumlah First Author","Score KPI"];
function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
}
function showEBCTable() { showFacultyTable("EBC"); }
function showFacultyTable(dept, displayName=dept) {
  selectedFacultyDept=dept;
  const ebcFaculty=datasetFaculty(dept);
  modalTitle.textContent = `Detail ${displayName} — Data Dosen dan Score KPI`;
  modalBody.innerHTML = `
    <div class="table-toolbar">
      <input id="facultySearch" type="search" placeholder="Cari kode atau nama dosen..." aria-label="Cari data dosen EBC">
      ${adminMode?`<button id="addFaculty" type="button" class="admin-btn">+ Tambah Dosen</button>`:""}
      <button id="exportFaculty" type="button">Export CSV</button>
    </div>
    <div class="table-scroll">
      <table class="faculty-table">
        <thead><tr>${facultyHeaders.map((h,i) => `<th scope="col" data-index="${i}" title="Klik untuk mengurutkan">${escapeHtml(h)}</th>`).join("")}${adminMode?'<th>Aksi</th>':''}</tr></thead>
        <tbody id="facultyRows"></tbody>
      </table>
    </div>
    <p class="table-meta" id="facultyCount"></p>
    <p class="table-meta">Klik nama dosen untuk melihat publikasinya. ${adminMode?"Gunakan Edit / Hapus untuk memperbarui data FM.":""}</p>`;
  const search = document.getElementById("facultySearch");
  let sortIndex = -1, ascending = true;
  function getVisibleRows() {
    const query = search.value.toLocaleLowerCase("id-ID").trim();
    const result = ebcFaculty.filter(row => row.some(v => v.toLocaleLowerCase("id-ID").includes(query)));
    if (sortIndex >= 0) result.sort((a,b) => {
      const x = a[sortIndex], y = b[sortIndex];
      const result = [5,6,7,8].includes(sortIndex)
        ? parseFloat(x.replace(",",".")) - parseFloat(y.replace(",","."))
        : x.localeCompare(y,"id",{numeric:true});
      return ascending ? result : -result;
    });
    return result;
  }
  function updateRows() {
    const rows = getVisibleRows();
    document.getElementById("facultyRows").innerHTML = rows.map(row =>
      `<tr class="${String(row[8]??"").trim()!=="" && safeNumber(row[8]) === 0 ? "kpi-zero" : ""}">${row.map((v,i) => `<td class="${i>=5?'num':i===2||i===3?'center':''}">${i===1 ? `<button type="button" class="faculty-link" data-faculty-code="${escapeHtml(row[0])}" title="Lihat detail publikasi ${escapeHtml(v)}">${escapeHtml(v)}</button>` : escapeHtml(v)}</td>`).join("")}${adminMode?`<td><div class="admin-table-buttons"><button type="button" data-edit-faculty="${escapeHtml(row[0])}">Edit</button><button type="button" class="remove" data-delete-faculty="${escapeHtml(row[0])}">Hapus</button></div></td>`:""}</tr>`
    ).join("") || `<tr><td colspan="${adminMode?10:9}" class="center">Data tidak ditemukan.</td></tr>`;
    document.getElementById("facultyCount").textContent = `Menampilkan ${rows.length} dari ${ebcFaculty.length} dosen`;
  }
  document.getElementById("facultyRows").addEventListener("click", event => {
    const link = event.target.closest("button[data-faculty-code]");
    const edit=event.target.closest("[data-edit-faculty]");
    const del=event.target.closest("[data-delete-faculty]");
    if(edit && adminMode) {
      const idx=ebcFaculty.findIndex(r=>r[0]===edit.dataset.editFaculty);
      if(idx>=0) openRowEditor("faculty",dept,idx); return;
    }
    if(del && adminMode) {
      const idx=ebcFaculty.findIndex(r=>r[0]===del.dataset.deleteFaculty);
      if(idx>=0) deleteRow("faculty",dept,idx); return;
    }
    if (!link) return;
    const faculty = ebcFaculty.find(row => row[0] === link.dataset.facultyCode);
    if (faculty) showFacultyPublications(faculty[0], faculty[1],false,dept);
  });
  if(adminMode) document.getElementById("addFaculty").addEventListener("click",()=>openRowEditor("faculty",dept,null));
  search.addEventListener("input", updateRows);
  modalBody.querySelectorAll("th[data-index]").forEach(th => th.addEventListener("click", () => {
    const index = Number(th.dataset.index);
    ascending = index === sortIndex ? !ascending : true;
    sortIndex = index;
    updateRows();
  }));
  document.getElementById("exportFaculty").addEventListener("click", () => {
    const rows = [facultyHeaders, ...getVisibleRows()];
    const csv = "\ufeff" + rows.map(row => row.map(v => `"${String(v).replace(/"/g,'""')}"`).join(";")).join("\r\n");
    const url = URL.createObjectURL(new Blob([csv],{type:"text/csv;charset=utf-8;"}));
    const link = document.createElement("a");
    link.href = url; link.download = `detail_${dept}_dosen_${yearFilter.value}.csv`; link.click();
    URL.revokeObjectURL(url);
  });
  updateRows();
  modal.classList.add("open");
  search.focus();
}


// Kolom tabel publikasi FM.
const publicationHeaders = [
  "Author", "FM Author", "Kode Dosen", "S/F", "Dept", "Kampus", "First Author",
  "Sumber paper", "Bobot", "Submitted", "St2024", "Jenis", "Tipe Publikasi",
  "Title", "Scopus Year", "Tanggal Pelaporan", "Source title", "Quartile Jurnal", "Prodi MHS"
];
// Kolom yang ditampilkan: data asli tetap memakai indeks 19 kolom untuk
// kompatibilitas Excel dan backup lama. S/F, Kampus, Tanggal Pelaporan,
// dan Prodi MHS tidak ditampilkan di web.
const visiblePublicationIndices = [0,1,2,4,6,7,8,9,10,11,12,13,14,16,17];
const visiblePublicationHeaders = visiblePublicationIndices.map(i => publicationHeaders[i]);
const ebcPublicationRecords = [
  [
    "FAIRUZ IQBAL MAULANA, PRISKA ARINDYA PURNAMA, (Vicky Angga Cahyana), MIFTAHUL HAMIM, AGUNG PURNOMO, (Puput Dani Prasetyo Adi)",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Penelitian Mandiri",
    "0.25",
    "Scopus FM",
    "3Accepted",
    "Seminar",
    "Scopus",
    "From Explanation to Prediction: Machine Learning Classification and Behavioral Segmentation of Digital Payment Continuance among QRIS Users in Indonesian MSMEs",
    "2026",
    "46289",
    "2026 12th International Conference on Education and Technology (ICET)",
    "Proceeding"
  ],
  [
    "PRISKA ARINDYA PURNAMA, (Vicky Angga Cahyana), FAIRUZ IQBAL MAULANA, MIFTAHUL HAMIM, AGUNG PURNOMO, (Puput Dani Prasetyo Adi)",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Penelitian Mandiri",
    "0.25",
    "Scopus FM",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Strategic Priorities for Digital Payment Continuance in MSMEs: An Importance-Performance Map Analysis Integrating PLS-SEM and IPMA on QRIS Adoption",
    "2026",
    "46289",
    "2026 12th International Conference on Education and Technology (ICET)",
    "Proceeding"
  ],
  [
    "ANDI PRAMONO, BAMBANG KARTONO K, FAIRUZ IQBAL MAULANA, AGUNG PURNOMO, (Muhamad Ezran)",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Hibah Internal: PIB-T",
    "0.25",
    "Scopus FM",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Design and Implementation of MicrocontrollerBased Smart Furniture with Adaptive Ambient Lighting for Elderly Fall Prevention",
    "2026",
    "46253",
    "2026 13th International Conference on ICT for Smart Society (ICISS)",
    "Proceeding"
  ],
  [
    "(CALISTA KALLEYA), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Penelitian Mandiri",
    "1",
    "Scopus FM",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Tech Entrepreneurship for Local Language Preservation: Developing a Mobile Learning Application through Challenge-Based Digital Social Interaction",
    "2026",
    "6/21/2026",
    "2026 13th International Conference on ICT for Smart Society (ICISS)",
    "Proceeding"
  ],
  [
    "(VINCE XERYL AHLIAS), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Business Creation",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Understanding the Roles of Entrepreneurship in Cultural Tourism: A Systematic Review with Global Delphi-Validated Grounded Theory Insights",
    "2026",
    "6/22/2026",
    "2026 13th International Conference of the International Association of Cultural and Digital Tourism (IACUDIT) 2026",
    "Proceeding"
  ],
  [
    "(ROBBY PANGESTU), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Business Creation",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Enablers of Entrepreneurship in Digital Tourism: A Systematic Review, Global Delphi Validation, and Grounded Theory Synthesis",
    "2026",
    "6/22/2026",
    "2026 13th International Conference of the International Association of Cultural and Digital Tourism (IACUDIT) 2026",
    "Proceeding"
  ],
  [
    "(JESSLYN EMMANUELA NJOTO PRAWIRO), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - Business Creation",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Big Data Analytics Enablers in Entrepreneurship: A Global Delphi-Validated Systematic Review and Grounded-Theory Synthesis",
    "2026",
    "5/27/2026",
    "2026 7th International Conference of Accounting & Business (iCAB) 2026",
    "Proceeding"
  ],
  [
    "(GREGORIUS SAMODRA BHATO RATU), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - Business Creation",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Enablers of Strategic Entrepreneurship in Family Business: A Systematic Review with Grounded Theory and Delphi Validation",
    "2026",
    "5/11/2026",
    "2026 International Scientific Conference on Business and Economics (ISCBE)",
    "Proceeding"
  ],
  [
    "(RIDHA OLIVIA RAMADHANI), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - Business Creation",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Strategic Entrepreneurship in the Digital Transformation of Entrepreneurial Ventures: Identifying Critical Success Factors Through Systematic Literature Review, Grounded Theory, and Global Delphi",
    "2026",
    "5/11/2026",
    "2026 International Scientific Conference on Business and Economics (ISCBE)",
    "Proceeding"
  ],
  [
    "(AJENG PRAMESTI SETIA WIBOWO), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - BBS UG - Entrepreneurship",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "A Systematic Literature Review and Delphi Study on Entrepreneurship Roles in Civil Infrastructure: Toward a Future Research Agenda",
    "2026",
    "2/19/2026",
    "2025 International Conferences on Digital Technology Driven Engineering (ICDTDE)",
    "Proceeding"
  ],
  [
    "(CASILDA AULIA RAHMADINA), AGUNG PURNOMO",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - BBS UG - Entrepreneurship",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Entrepreneurship Enablers in Civil Infrastructure: A Systematic Review and Delphi-Validated Synthesis",
    "2026",
    "2/17/2026",
    "2025 International Conferences on Digital Technology Driven Engineering (ICDTDE)",
    "Proceeding"
  ],
  [
    "(IMELIANA JOPI, SHANDY AULIA JAYA, HIZKYA RAKA PRIANANDA), AGUNG PURNOMO, YULIA MAGDALENA",
    "Agung Purnomo, S.P., MBA",
    "D5868",
    "BBS",
    "Business Creation",
    "MALANG",
    "N",
    "Skripsi Malang - BBS UG - Entrepreneurship",
    "1",
    "Scopus Mahasiswa",
    "3Accepted",
    "Seminar",
    "Scopus",
    "Success Factors of IoT-Enabled Digital Tools in Entrepreneurship: A Systematic Review Integrated with Delphi Validation",
    "2026",
    "1/1/2026",
    "2025 International Conferences on Digital Technology Driven Engineering (ICDTDE)",
    "Proceeding"
  ]
];

const STORE_KEY = "scopus_dashboard_editable_v1";
const summaryIds = ["milestoneTarget","milestoneActual","fmTarget","fmActual","studentTarget","studentActual","fmPercent"];
const defaultSummary = ["1250","1255","15","17","15","17","75 %"];
const defaultTitles = ["MILESTONE","PUBLIKASI SCOPUS FM","PUBLIKASI SCOPUS MHS","% FM HAVE SCOPUS"];
// Judul otomatis per indikator. Judul yang sudah diedit pengguna tetap diutamakan.
const indicatorSummaryTitles = {
  all: ["MILESTONE", "PUBLIKASI SCOPUS FM", "PUBLIKASI SCOPUS MHS", "% FM HAVE SCOPUS"],
  publication: ["MILESTONE", "PUBLIKASI SCOPUS FM", "PUBLIKASI SCOPUS MHS", "% FM HAVE SCOPUS"],
  fm: ["MILESTONE", "PUBLIKASI SCOPUS FM", "PUBLIKASI SCOPUS MHS", "% FM HAVE SCOPUS"],
  mhs: ["MILESTONE", "PUBLIKASI SCOPUS FM", "PUBLIKASI SCOPUS MHS", "% FM HAVE SCOPUS"],
  ntr: ["OTHER REVENUE STREAM (NTR)", "NTR — FM", "NTR — MHS", "NTR — CAPAIAN"],
  citation: ["NEW CITATION", "NEW CITATION — FM", "NEW CITATION — MHS", "NEW CITATION — CAPAIAN"],
  research: ["RESEARCH PROGRAM", "RESEARCH PROGRAM — FM", "RESEARCH PROGRAM — MHS", "RESEARCH PROGRAM — CAPAIAN"],
  ip: ["# OF NEW IP", "NEW IP — FM", "NEW IP — MHS", "NEW IP — CAPAIAN"],
  vrtt: ["ALOKASI BUDGET VRTT", "TARGET BUDGET VRTT", "REALISASI BUDGET VRTT", "% SERAPAN BUDGET VRTT"]
};
function automaticTitles() {
  return indicatorSummaryTitles[indicatorFilter.value] || defaultTitles;
}
function visibleSummaryTitles(data) {
  const fallback = automaticTitles();
  return fallback.map((title,i) => {
    const custom = data?.titles?.[i];
    return typeof custom === "string" && custom.trim() ? custom : title;
  });
}

const cardFields = [
  ["displayName","Nama Grafik yang Ditampilkan"],["dept","Kode Jurusan untuk Data Detail"],["group","Kategori (FM/MHS)"],["score","Score"],
  ["fmCount","Informasi jumlah FM"],["quarter","Quartal Grafik (Q1–Q4)"],["target","Target"],["actual","Realisasi"],
  ["fmPercent","% FM Have Scopus"],["papers","Bobot Paper Scopus"],
  ["journals","Bobot Jurnal Scopus"],["conferences","Bobot Konferensi"],
  ["bookChapter","Bobot Book Chapter"],["fullBook","Bobot Full Book"],
  ["fmScopusCount","Jumlah FM memiliki Scopus"],["fmTotal","Jumlah FM total"],
  ["paperTitles","Jumlah judul Paper"],["journalTitles","Jumlah judul Jurnal"],
  ["conferenceTitles","Jumlah judul Konferensi"],["chapterTitles","Jumlah judul Book Chapter"],
  ["bookTitles","Jumlah judul Full Book"]
];
const deepCopy = obj => JSON.parse(JSON.stringify(obj));
const initialCards = departments.map(item => ({
  ...item, fmScopusCount:5, fmTotal:10,
  paperTitles:14, journalTitles:14, conferenceTitles:14, chapterTitles:14, bookTitles:14
}));
const initialData = {
  updated: "22 Oktober 2026",
  years: {
    "2026": {
      publication: {
        summary: deepCopy(defaultSummary), titles: deepCopy(defaultTitles),
        cards: deepCopy(initialCards),
        facultyByDept: {EBC:deepCopy(ebcFaculty)},
        publicationsByDept: {EBC:deepCopy(ebcPublicationRecords)}
      }
    }
  }
};
let appData = deepCopy(initialData);
try {
  const stored = localStorage.getItem(STORE_KEY);
  if (stored) {
    const parsed = JSON.parse(stored);
    if (parsed && parsed.years && typeof parsed.years === "object") appData = parsed;
  }
} catch(err) { console.warn("Penyimpanan browser tidak tersedia:",err); }
let adminMode = false;
let selectedCardId = "";
let selectedFacultyDept = "EBC";
// Publication FM dan Publikasi Mahasiswa menggunakan basis data publikasi yang sama,
// tetapi grafik difilter berdasarkan kategori. Indikator lain memiliki dataset
// masing-masing per tahun; data lama tetap dapat dibuka tanpa migrasi.
function isPublicationIndicator() { return indicatorFilter.value === "fm" || indicatorFilter.value === "mhs"; }
function dataKey() { return isPublicationIndicator() ? "publication" : indicatorFilter.value; }
function activePublicationGroup() { return indicatorFilter.value === "mhs" ? "MHS" : "FM"; }
function normalizeChartQuarter(value) {
  const normalized=String(value??"").trim().toUpperCase();
  return ["Q1","Q2","Q3","Q4"].includes(normalized) ? normalized : "";
}
function indicatorCards(data) {
  const cards=data?.cards||[];
  if(!isPublicationIndicator()) return cards;
  return cards.filter(card=>String(card.group||"FM").toUpperCase()===activePublicationGroup());
}
function visiblePublicationCards(data) {
  const cards=indicatorCards(data);
  if(quarterFilter.value==="all") return cards;
  return cards.filter(card=>normalizeChartQuarter(card.quarter)===quarterFilter.value);
}
function currentDataset(create=false) {
  const y = yearFilter.value, k = dataKey();
  if (!appData.years[y] && create) appData.years[y] = {};
  if (!appData.years[y]) return null;
  if (!appData.years[y][k] && create) appData.years[y][k] = {
    summary: Array(7).fill(""), titles: deepCopy(automaticTitles()),
    cards:[], facultyByDept:{}, publicationsByDept:{}
  };
  return appData.years[y][k] || null;
}
function persist() {
  try { localStorage.setItem(STORE_KEY, JSON.stringify(appData)); showStatus("Perubahan berhasil disimpan di browser."); }
  catch(err) { showStatus("Penyimpanan browser gagal. Segera Export JSON untuk mencadangkan data.",true); }
}
function showStatus(message,error=false) {
  const status = document.getElementById("adminStatus");
  status.textContent = message;
  status.style.color = error ? "#b11d1d" : "#216038";
}
function safeNumber(value) { const n = Number(String(value).replace(",",".")); return Number.isFinite(n) ? n : 0; }
function datasetFaculty(dept) {
  const data = currentDataset();
  return data?.facultyByDept?.[dept] || [];
}
function datasetPublications(dept) {
  const data = currentDataset();
  return data?.publicationsByDept?.[dept] || [];
}
function getInputFields(container, definitions, values) {
  container.innerHTML = definitions.map(([key,label],index) =>
    `<label class="admin-field">${escapeHtml(label)}
      <input data-field="${escapeHtml(key)}" value="${escapeHtml(values[index] ?? "")}" type="text">
    </label>`).join("");
}
function readInputFields(container,definitions) {
  return definitions.map(([key]) => container.querySelector(`[data-field="${key}"]`).value.trim());
}
function renderAdmin() {
  const data = currentDataset();
  document.getElementById("adminFields").classList.remove("hidden-admin");
  document.getElementById("chartAdminFields").classList.toggle("hidden-admin",!data);
  document.getElementById("createDataset").disabled = !!data;
  const fields = [];
  for(let i=0;i<4;i++) fields.push([`title${i}`,`Judul Ringkasan ${i+1}`]);
  summaryIds.forEach((id,i)=>fields.push([id,[
    "Milestone Target","Milestone Realisasi","FM Target","FM Realisasi","MHS Target","MHS Realisasi","Persentase / Nilai KPI"
  ][i]]));
  getInputFields(document.getElementById("summaryEditor"),fields,[
    ...visibleSummaryTitles(data), ...(data?.summary || Array(7).fill(""))
  ]);
  const q=quarterFilter.value;
  const qPanel=document.getElementById("quarterSummaryPanel");
  qPanel.classList.toggle("hidden-admin",q==="all");
  if(q!=="all") {
    document.getElementById("quarterSummaryEditorTitle").textContent=`Ringkasan ${q} — Tahun ${yearFilter.value}`;
    const stored=data?.quarterSummaries?.[q]||Array(7).fill("");
    const auto=quarterSummaryForDataset(data,q);
    const qFields=summaryIds.map((id,i)=>["q"+i,["Milestone Target","Milestone Realisasi","FM Target","FM Realisasi","MHS Target","MHS Realisasi","% FM Have Scopus / Nilai KPI"][i]]);
    getInputFields(document.getElementById("quarterSummaryEditor"),qFields,stored);
    document.querySelectorAll('#quarterSummaryEditor input').forEach((input,i)=>{
      input.placeholder=auto[i]===null?"Belum ada data (isi manual)":`Otomatis: ${auto[i]}`;
    });
  }
  if (!data) {
    showStatus("Data indikator ini belum tersedia. Anda dapat mengedit judul dan menyimpannya untuk membuat ringkasan baru.");
    return;
  }
  const selector = document.getElementById("cardSelector");
  // Editor tetap menampilkan semua grafik indikator agar label kuartal lama bisa diatur.
  const cardsInIndicator = indicatorCards(data);
  if (!cardsInIndicator.some(c=>c.id===selectedCardId)) selectedCardId=cardsInIndicator[0]?.id||"";
  selector.innerHTML = cardsInIndicator.map(c=>`<option value="${escapeHtml(c.id)}">${escapeHtml(c.dept)} — ${escapeHtml(c.group)} (${escapeHtml(c.id)})</option>`).join("");
  selector.value=selectedCardId;
  renderCardEditor();
}
function renderCardEditor() {
  const data=currentDataset(), card=data?.cards.find(c=>c.id===selectedCardId);
  document.getElementById("cardEditor").innerHTML = "";
  if (!card) return;
  getInputFields(document.getElementById("cardEditor"),cardFields,cardFields.map(([key])=>key==="displayName" ? (card.displayName||card.dept) : card[key]));
  const quarterInput=document.querySelector('#cardEditor [data-field="quarter"]');
  if(quarterInput) {
    const select=document.createElement("select");
    select.dataset.field="quarter";
    select.setAttribute("aria-label","Quartal Grafik");
    select.innerHTML=`<option value="">Belum ditentukan</option>${["Q1","Q2","Q3","Q4"].map(q=>`<option value="${q}">${q}</option>`).join("")}`;
    select.value=normalizeChartQuarter(card.quarter);
    quarterInput.replaceWith(select);
  }
}
function generateId() { return "card-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2,6); }
function downloadFile(name,body,type) {
  const url=URL.createObjectURL(new Blob([body],{type}));
  const link=document.createElement("a"); link.href=url; link.download=name; document.body.appendChild(link); link.click(); link.remove();
  setTimeout(()=>URL.revokeObjectURL(url),1000);
}
function openRowEditor(kind,dept,rowIndex,facultyCode=null) {
  const data=currentDataset(true);
  const isFaculty=kind==="faculty";
  const headings=isFaculty?facultyHeaders:visiblePublicationHeaders;
  const indices=isFaculty?facultyHeaders.map((_,i)=>i):visiblePublicationIndices;
  const arr=isFaculty ? (data.facultyByDept[dept] ||= []) : (data.publicationsByDept[dept] ||= []);
  const row=rowIndex===null ? (isFaculty?facultyHeaders:publicationHeaders).map(()=> "") : arr[rowIndex];
  if(!row) return;
  modalTitle.textContent = `${rowIndex===null?"Tambah":"Edit"} ${isFaculty?"Dosen":"Publikasi"} — ${dept}`;
  modalBody.innerHTML = `
    <form id="rowEditor" class="edit-form">
      ${headings.map((h,i)=>{ const originalIndex=indices[i]; return `<label class="admin-field ${[0,13,16].includes(originalIndex)&&!isFaculty?'wide':''}">${escapeHtml(h)}
        ${!isFaculty && [0,13,16].includes(originalIndex) ? `<textarea name="field${originalIndex}" rows="3">${escapeHtml(row[originalIndex]||"")}</textarea>` : `<input name="field${originalIndex}" value="${escapeHtml(row[originalIndex]||"")}" ${isFaculty&&i===0?'required':''}>`}
      </label>`; }).join("")}
      <div class="edit-actions">
        <button type="submit" class="admin-btn">Simpan Perubahan</button>
        <button type="button" class="admin-btn alt" id="cancelRowEdit">Batal</button>
      </div>
    </form>`;
  document.getElementById("cancelRowEdit").onclick=()=>isFaculty?showFacultyTable(dept):showFacultyPublications(facultyCode||row[2],"",false,dept);
  document.getElementById("rowEditor").addEventListener("submit",e=>{
    e.preventDefault();
    // Preserve the hidden fields when editing existing publication records.
    const next=isFaculty ? [...row] : Array.from({length:publicationHeaders.length},(_,i)=>row[i]??"");
    indices.forEach(i=>{next[i]=e.target.elements[`field${i}`].value.trim();});
    if(isFaculty) {
      if(data.facultyByDept[dept].some((r,i)=>r[0]===next[0] && i!==rowIndex)) {
        alert("Kode dosen sudah digunakan. Gunakan kode unik.");return;
      }
    } else if(facultyCode) {
      next[2]=facultyCode;
    }
    if(rowIndex===null) arr.push(next); else arr[rowIndex]=next;
    persist();
    isFaculty?showFacultyTable(dept):showFacultyPublications(facultyCode||next[2],"",false,dept);
  });
  modal.classList.add("open");
}
function deleteRow(kind,dept,index,facultyCode) {
  if(!confirm("Hapus baris ini? Perubahan akan tersimpan di browser.")) return;
  const data=currentDataset();
  const arr=kind==="faculty"?data.facultyByDept[dept]:data.publicationsByDept[dept];
  arr.splice(index,1);
  persist();
  kind==="faculty"?showFacultyTable(dept):showFacultyPublications(facultyCode,"",false,dept);
}


// ----------------------------------------------------------
// Import publikasi Excel (offline): XLSX ZIP + CSV.
// ----------------------------------------------------------
let stagedExcelImport=null;
const publicationColumnAliases = {
  "fm author":["fmauthor","nama fm","nama dosen","fmauthorname"],
  "kode dosen":["kodedosen","kodefm","lecturercode","id dosen"],
  "title":["title","judul","judulpaper","judulpublikasi","papertitle"],
  "scopus year":["scopusyear","tahunscopus","tahunpublikasi","publicationyear","tahun"],
  "sumber paper":["sumberpaper","sumberpublikasi","sumberpenelitian"],
  "quartile jurnal":["quartilejurnal","quartile","kuartiljurnal","qjurnal"],
  "first author":["firstauthor","penulispertama"],
  "prodi mhs":["prodimhs","programstudimahasiswa"]
};
function normalizeExcelHeader(value) {
  return String(value??"").toLowerCase().replace(/&nbsp;|\u00a0/g," ")
    .replace(/[^a-z0-9]/g,"");
}
function getExcelColumnMap(headerRow) {
  const map = {};
  publicationHeaders.forEach((h,i)=>{
    const aliases = [h,...(publicationColumnAliases[h.toLowerCase()]||[])].map(normalizeExcelHeader);
    const col = headerRow.findIndex(v=>aliases.includes(normalizeExcelHeader(v)));
    if(col>=0) map[i]=col;
  });
  return map;
}
function findExcelTable(sheetRows) {
  for(let rowIndex=0;rowIndex<Math.min(sheetRows.length,30);rowIndex++) {
    const mapping=getExcelColumnMap(sheetRows[rowIndex]||[]);
    if([1,2,13].every(col=>mapping[col]!==undefined)) return {headerIndex:rowIndex,mapping};
  }
  return null;
}
function parseDelimited(textValue) {
  const text=String(textValue).replace(/^\uFEFF/,"");
  const firstLine=text.split(/\r?\n/).find(l=>l.trim())||"";
  const counts=[",",";","\t"].map(d=>({d,n:firstLine.split(d).length-1}));
  const delimiter=counts.sort((a,b)=>b.n-a.n)[0].d;
  const rows=[];let row=[],field="",quoted=false;
  for(let i=0;i<text.length;i++) {
    const c=text[i];
    if(c==='"') {
      if(quoted && text[i+1]==='"') {field+='"';i++;}
      else quoted=!quoted;
    } else if(c===delimiter && !quoted) {row.push(field);field="";}
    else if((c==='\n'||c==='\r') && !quoted) {
      if(c==='\r'&&text[i+1]==='\n') i++;
      row.push(field);rows.push(row);row=[];field="";
    } else field+=c;
  }
  if(field!==""||row.length) {row.push(field);rows.push(row);}
  return rows;
}
function xmlElements(node,tag) { return Array.from(node.getElementsByTagNameNS("*",tag)); }
function xmlFirst(node,tag) { return xmlElements(node,tag)[0]||null; }
function excelColumnNumber(ref) {
  let n=0;const m=String(ref||"").match(/^[A-Z]+/i);
  if(!m) return -1;
  for(const c of m[0].toUpperCase()) n=n*26+c.charCodeAt(0)-64;
  return n-1;
}
function parseXML(text,path) {
  const xml=new DOMParser().parseFromString(text,"application/xml");
  if(xml.getElementsByTagName("parsererror").length) throw Error("Struktur XML Excel tidak valid: "+path);
  return xml;
}
async function readZipEntries(buffer) {
  const view=new DataView(buffer), bytes=new Uint8Array(buffer);
  let eocd=-1;
  for(let i=bytes.length-22;i>=Math.max(0,bytes.length-65557);i--) {
    if(view.getUint32(i,true)===0x06054b50){eocd=i;break;}
  }
  if(eocd<0) throw Error("File tidak dikenali sebagai XLSX yang valid.");
  const entryCount=view.getUint16(eocd+10,true);
  let offset=view.getUint32(eocd+16,true);
  const decoder=new TextDecoder("utf-8"), entries=new Map();
  for(let i=0;i<entryCount;i++) {
    if(view.getUint32(offset,true)!==0x02014b50) throw Error("Direktori ZIP Excel rusak.");
    const method=view.getUint16(offset+10,true);
    const compressedSize=view.getUint32(offset+20,true);
    const nameLength=view.getUint16(offset+28,true);
    const extraLength=view.getUint16(offset+30,true);
    const commentLength=view.getUint16(offset+32,true);
    const localOffset=view.getUint32(offset+42,true);
    const name=decoder.decode(bytes.slice(offset+46,offset+46+nameLength));
    entries.set(name,{method,compressedSize,localOffset});
    offset+=46+nameLength+extraLength+commentLength;
  }
  async function getText(name) {
    const e=entries.get(name);
    if(!e) throw Error("Komponen Excel tidak ditemukan: "+name);
    const off=e.localOffset;
    if(view.getUint32(off,true)!==0x04034b50) throw Error("Header ZIP Excel rusak.");
    const start=off+30+view.getUint16(off+26,true)+view.getUint16(off+28,true);
    const compressed=bytes.slice(start,start+e.compressedSize);
    let decoded;
    if(e.method===0) decoded=compressed;
    else if(e.method===8) {
      if(typeof DecompressionStream==="undefined") throw Error("Browser belum mendukung pembacaan XLSX. Gunakan Chrome/Edge terbaru atau CSV.");
      try {
        const stream=new Blob([compressed]).stream().pipeThrough(new DecompressionStream("deflate-raw"));
        decoded=new Uint8Array(await new Response(stream).arrayBuffer());
      } catch(err) {throw Error("Gagal membuka kompresi XLSX. Gunakan browser terbaru atau simpan sebagai CSV.");}
    } else throw Error("Metode kompresi XLSX tidak didukung.");
    return decoder.decode(decoded);
  }
  return {getText,entries};
}
async function parseXlsx(buffer) {
  const zip=await readZipEntries(buffer);
  const workbook=parseXML(await zip.getText("xl/workbook.xml"),"workbook.xml");
  const relationships=parseXML(await zip.getText("xl/_rels/workbook.xml.rels"),"workbook.xml.rels");
  const targets=new Map(xmlElements(relationships,"Relationship").map(rel=>[rel.getAttribute("Id"),rel.getAttribute("Target")]));
  let strings=[];
  if(zip.entries.has("xl/sharedStrings.xml")) {
    const xml=parseXML(await zip.getText("xl/sharedStrings.xml"),"sharedStrings.xml");
    strings=xmlElements(xml,"si").map(si=>xmlElements(si,"t").map(t=>t.textContent).join(""));
  }
  const sheets=[];
  for(const sheet of xmlElements(workbook,"sheet")) {
    const id=sheet.getAttribute("r:id")||sheet.getAttributeNS("http://schemas.openxmlformats.org/officeDocument/2006/relationships","id");
    let path=targets.get(id)||"";
    if(path.startsWith("/")) path=path.slice(1);
    else if(!path.startsWith("xl/")) path="xl/"+path;
    path=path.replace(/\\/g,"/").replace(/\/\.\//g,"/");
    if(!zip.entries.has(path)) continue;
    const xml=parseXML(await zip.getText(path),path);
    const rows=[];
    for(const rowNode of xmlElements(xml,"row")) {
      const cells=[];
      for(const cell of xmlElements(rowNode,"c")) {
        const index=excelColumnNumber(cell.getAttribute("r"));
        if(index<0) continue;
        const type=cell.getAttribute("t");
        const valueNode=xmlFirst(cell,"v");
        let value=valueNode?.textContent||"";
        if(type==="s") value=strings[Number(value)]??"";
        else if(type==="inlineStr") value=xmlElements(cell,"t").map(t=>t.textContent).join("");
        else if(type==="b") value=value==="1"?"TRUE":"FALSE";
        cells[index]=String(value);
      }
      rows.push(cells);
    }
    sheets.push({name:sheet.getAttribute("name")||path,rows});
  }
  return sheets;
}
function normalizeImportYear(value) {
  const match=String(value??"").trim().match(/\b(2023|2024|2025|2026)\b/);
  return match?match[1]:null;
}
function publicationKey(row) {
  return `${String(row[2]||"").trim().toUpperCase()}|${String(row[13]||"").trim().toLocaleLowerCase("id-ID").replace(/\s+/g," ")}|${row[14]}`;
}
function inferFacultyDept(row, knownDepts) {
  const code=String(row[2]||"").trim().toUpperCase();
  if(knownDepts.has(code)) return knownDepts.get(code);
  const dept=String(row[4]||"").toLowerCase().replace(/\s+/g," ").trim();
  if(/business creation|entrepreneur|\bbc\b|\bbbs\b/.test(dept)) return "EBC";
  if(/computer science|informat|information system|\bcs\b|\bsocs\b/.test(dept)) return "CS";
  if(/visual communication|graphic design|desain komunikasi visual|\bdkv\b/.test(dept)) return "DKV";
  return "Lainnya";
}
function getKnownFacultyDepartments() {
  const known=new Map();
  for(const groups of Object.values(appData.years)) {
    for(const dataset of Object.values(groups)) {
      for(const [dept,rows] of Object.entries(dataset.facultyByDept||{})) {
        rows.forEach(row=>{if(row[0]&&!known.has(String(row[0]).toUpperCase())) known.set(String(row[0]).toUpperCase(),dept);});
      }
    }
  }
  return known;
}
function collectExcelRecords(sheets) {
  const result=[], skipped=[], recognizedSheets=[];
  for(const sheet of sheets) {
    const table=findExcelTable(sheet.rows);
    if(!table) continue;
    recognizedSheets.push(sheet.name);
    for(let index=table.headerIndex+1;index<sheet.rows.length;index++) {
      const raw=sheet.rows[index]||[];
      if(!raw.some(cell=>String(cell??"").trim())) continue;
      const row=publicationHeaders.map((_,i)=>String(raw[table.mapping[i]]??"").trim());
      const code=row[2].toUpperCase();
      const year=normalizeImportYear(row[14]) || (row[14]===""?yearFilter.value:null);
      if(!code || !row[13] || !year) {
        skipped.push(`${sheet.name} baris ${index+1}: ${!code?"Kode Dosen":!row[13]?"Title":"Scopus Year"} tidak valid.`);
        continue;
      }
      row[2]=code;row[14]=year;
      result.push(row);
    }
  }
  if(!recognizedSheets.length) throw Error("Tidak ditemukan sheet dengan kolom Kode Dosen, FM Author, dan Title. Periksa header Excel.");
  const seen=new Set(),unique=[];let duplicates=0;
  for(const row of result) {
    const key=publicationKey(row);
    if(seen.has(key)) {duplicates++;continue;}
    seen.add(key);unique.push(row);
  }
  return {rows:unique,duplicates,skipped,sheets:recognizedSheets};
}
function createPublicationDataset(year) {
  appData.years[year] ||= {};
  appData.years[year].publication ||= {
    summary:Array(7).fill(""),titles:deepCopy(indicatorSummaryTitles.publication),
    cards:[],facultyByDept:{},publicationsByDept:{}
  };
  return appData.years[year].publication;
}
function previewExcelImport(parsed,fileName) {
  stagedExcelImport=parsed;
  const el=document.getElementById("excelImportPreview");
  const years=[...new Set(parsed.rows.map(row=>row[14]))].sort();
  const fm=new Set(parsed.rows.map(row=>row[2]));
  const known=getKnownFacultyDepartments();
  const counts={};
  parsed.rows.forEach(row=>{const dept=inferFacultyDept(row,known);counts[dept]=(counts[dept]||0)+1;});
  el.classList.remove("hidden-admin");
  el.innerHTML=`
    <h4>Pratinjau Impor — ${escapeHtml(fileName)}</h4>
    <div class="excel-preview-stats">
      <div><strong>${parsed.rows.length}</strong><small>Publikasi valid</small></div>
      <div><strong>${fm.size}</strong><small>FM unik</small></div>
      <div><strong>${years.join(", ")||"—"}</strong><small>Tahun Scopus</small></div>
      <div><strong>${parsed.skipped.length+parsed.duplicates}</strong><small>Baris dilewati / duplikat</small></div>
    </div>
    <div class="excel-preview-detail">
      <b>Sheet:</b> ${escapeHtml(parsed.sheets.join(", "))}<br>
      <b>Kelompok jurusan:</b> ${escapeHtml(Object.entries(counts).map(([dept,count])=>`${dept}: ${count}`).join(" • "))}<br>
      <b>Metode:</b> ${document.getElementById("excelImportMode").value==="merge"?"Gabungkan dan perbarui berdasarkan Kode Dosen + Title + Tahun":"Ganti seluruh publikasi pada tahun yang ada dalam file"}
    </div>
    ${parsed.skipped.length?`<p class="excel-warning">${parsed.skipped.length} baris tidak valid dilewati. Contoh: ${escapeHtml(parsed.skipped.slice(0,3).join(" | "))}</p>`:""}
    ${parsed.duplicates?`<p class="excel-warning">${parsed.duplicates} publikasi duplikat dalam file tidak dimasukkan dua kali.</p>`:""}
    <p class="admin-note">Nama FM baru akan ditambahkan otomatis ke daftar dosen. Nilai KPI dosen tidak dihitung atau ditimpa. Grafik dan ringkasan target tetap dipertahankan.</p>
    <div class="excel-preview-actions">
      <button type="button" class="admin-btn" id="applyExcelImport">✓ Terapkan ${parsed.rows.length} Publikasi</button>
      <button type="button" class="admin-btn alt" id="cancelExcelImport">Batal</button>
    </div>`;
  document.getElementById("applyExcelImport").addEventListener("click",applyExcelImport);
  document.getElementById("cancelExcelImport").addEventListener("click",()=>{
    stagedExcelImport=null;el.classList.add("hidden-admin");el.innerHTML="";
    document.getElementById("publicationExcelFile").value="";
  });
}
function applyExcelImport() {
  const parsed=stagedExcelImport;
  if(!parsed?.rows?.length) return;
  const mode=document.getElementById("excelImportMode").value;
  const years=[...new Set(parsed.rows.map(row=>row[14]))];
  const message=mode==="replace"
    ? `PERHATIAN: Semua data publikasi tahun ${years.join(", ")} akan DIGANTI dengan data Excel. Data publikasi lama yang tidak ada di file akan hilang. Lanjutkan?`
    : `Impor ${parsed.rows.length} publikasi untuk tahun ${years.join(", ")}? Publikasi yang cocok akan diperbarui, publikasi lain tetap tersimpan.`;
  if(!confirm(message)) return;
  const backup=deepCopy(appData);
  const known=getKnownFacultyDepartments();
  let inserted=0,updated=0,newFM=0;
  try {
    if(mode==="replace") years.forEach(year=>{
      const dataset=createPublicationDataset(year);
      dataset.publicationsByDept={};
    });
    for(const row of parsed.rows) {
      const year=row[14],dept=inferFacultyDept(row,known),code=row[2];
      const data=createPublicationDataset(year);
      data.publicationsByDept[dept] ||= [];
      data.facultyByDept[dept] ||= [];
      const existing=data.facultyByDept[dept].find(f=>String(f[0]).toUpperCase()===code);
      if(!existing) {
        // KPI kosong = belum diketahui, bukan nol.
        data.facultyByDept[dept].push([code,row[1]||code,row[4]||dept,"","","","","",""]);
        newFM++;
      } else if((!existing[1] || existing[1]===code) && row[1]) {
        existing[1]=row[1];
      }
      known.set(code,dept);
      const rows=data.publicationsByDept[dept];
      const key=publicationKey(row);
      const oldIndex=rows.findIndex(old=>publicationKey(old)===key);
      if(oldIndex>=0) {rows[oldIndex]=row;updated++;}
      else {rows.push(row);inserted++;}
      if(!data.cards.some(card=>card.dept===dept)) {
        data.cards.push({
          id:"excel-"+year+"-"+dept.toLowerCase().replace(/[^a-z0-9]/g,""),
          dept,displayName:dept,group:"FM",score:"",fmCount:"Data FM tersedia",
          target:0,actual:0,fmPercent:0,papers:0,journals:0,
          conferences:0,bookChapter:0,fullBook:0,fmScopusCount:0,fmTotal:0,
          paperTitles:0,journalTitles:0,conferenceTitles:0,chapterTitles:0,bookTitles:0
        });
      }
    }
    // Persistensi dicek eksplisit agar kegagalan kuota tidak terlihat sebagai sukses.
    localStorage.setItem(STORE_KEY,JSON.stringify(appData));
  } catch(err) {
    appData=backup;
    showStatus("Import gagal: "+err.message+". Data lama tetap dipertahankan. Coba kurangi ukuran file atau gunakan backup JSON.",true);
    return;
  }
  stagedExcelImport=null;
  document.getElementById("excelImportPreview").classList.add("hidden-admin");
  document.getElementById("excelImportPreview").innerHTML="";
  document.getElementById("publicationExcelFile").value="";
  renderCards();
  showStatus(`Import selesai: ${inserted} publikasi baru, ${updated} diperbarui, ${newFM} entri FM baru. Tahun: ${years.join(", ")}. Data tersimpan di browser.`);
}
document.getElementById("publicationExcelFile").addEventListener("change",async event=>{
  const file=event.target.files?.[0];
  if(!file) return;
  const preview=document.getElementById("excelImportPreview");
  preview.classList.remove("hidden-admin");
  preview.innerHTML="<p class='admin-note'>Membaca dan memvalidasi file…</p>";
  stagedExcelImport=null;
  try {
    if(file.size>35*1024*1024) throw Error("Ukuran file melebihi 35 MB.");
    let sheets;
    if(file.name.toLowerCase().endsWith(".csv")) {
      sheets=[{name:"CSV",rows:parseDelimited(await file.text())}];
    } else if(file.name.toLowerCase().endsWith(".xlsx")) {
      sheets=await parseXlsx(await file.arrayBuffer());
    } else throw Error("Format tidak didukung. Gunakan .xlsx atau .csv.");
    const parsed=collectExcelRecords(sheets);
    if(!parsed.rows.length) throw Error("Tidak ditemukan publikasi valid. Pastikan Kode Dosen, FM Author, Title, dan Scopus Year terisi.");
    previewExcelImport(parsed,file.name);
  } catch(err) {
    preview.innerHTML=`<p class="excel-error">${escapeHtml(err.message)}</p>`;
    showStatus("File belum diimpor. Periksa kesalahan pada pratinjau.",true);
  }
});
document.getElementById("excelImportMode").addEventListener("change",()=>{
  if(stagedExcelImport) previewExcelImport(stagedExcelImport,document.getElementById("publicationExcelFile").files[0]?.name||"Excel");
});
document.getElementById("downloadPublicationTemplate").addEventListener("click",()=>{
  const sample=visiblePublicationHeaders.map(h=>`"${String(h).replace(/"/g,'""')}"`).join(",");
  downloadFile("template_import_publikasi_FM.csv","\uFEFF"+sample+"\r\n","text/csv;charset=utf-8");
});

function exportCSV(headers, rows, filename) {
  const csv = "\ufeff" + [headers, ...rows].map(row => row.map(v => `"${String(v).replace(/"/g,'""')}"`).join(";")).join("\r\n");
  const url = URL.createObjectURL(new Blob([csv], {type:"text/csv;charset=utf-8;"}));
  const link = document.createElement("a");
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  link.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}
function showFacultyPublications(code, name, isExample = false, dept = "EBC") {
  const faculty = datasetFaculty(dept).find(r=>r[0]===code);
  name=name || faculty?.[1] || code;
  const allRows=datasetPublications(dept);
  const rows = allRows.filter(row => row[2] === code);
  const headers = visiblePublicationHeaders;
  modalTitle.textContent = `Publikasi FM — ${name}`;
  modalBody.innerHTML = `
    <div class="table-toolbar">
      <button type="button" class="secondary-btn" id="backToFaculty">← Kembali ke daftar dosen ${escapeHtml(dept)}</button>
      ${adminMode?`<button type="button" class="admin-btn" id="addPublication">+ Tambah Publikasi</button>`:""}
      <input id="publicationSearch" type="search" placeholder="Cari judul, sumber, atau author..." aria-label="Cari publikasi">
      <button type="button" id="exportPublications">Export CSV</button>
    </div>
    <section class="publication-filters" aria-label="Filter detail publikasi">
      <div class="publication-filters-head">
        <div>
          <p class="publication-filters-title">Filter Publikasi</p>
          <span class="publication-filters-subtitle">Pilih lebih dari satu opsi pada setiap filter; hasil tabel diperbarui otomatis</span>
        </div>
        <button type="button" id="resetPublicationFilters" class="publication-reset">Reset Semua Filter</button>
      </div>
      <div id="publicationFilterGrid" class="publication-filters-grid"></div>
    </section>
    <div class="table-scroll">
      <table class="faculty-table publication-table">
        <thead><tr>${headers.map(h => `<th scope="col">${escapeHtml(h)}</th>`).join("")}${adminMode?"<th>Aksi</th>":""}</tr></thead>
        <tbody id="publicationRows"></tbody>
      </table>
    </div>
    <p class="table-meta" id="publicationCount"></p>
    ${rows.length === 0 ? `<p class="table-empty">Belum ada rincian publikasi untuk <strong>${escapeHtml(name)}</strong> (${escapeHtml(code)}) dalam data yang diberikan. Format tabel sudah tersedia; data tidak diisi dengan publikasi milik dosen lain.</p>` : ''}
    `;
  const search = document.getElementById("publicationSearch");
  // Indeks mengikuti urutan kolom tabel publikasi, tanpa mengubah data sumber.
  const filterDefinitions = [
    {index:9, label:"Submitted"},
    {index:11, label:"Jenis"},
    {index:12, label:"Tipe Publikasi"},
    {index:17, label:"Quartile Jurnal"},
    {index:6, label:"First Author"},
    {index:7, label:"Sumber paper"}
  ];
  const filterGrid = document.getElementById("publicationFilterGrid");
  filterGrid.innerHTML = filterDefinitions.map(({index,label}) => {
    const values = [...new Set(rows.map(row => String(row[index] ?? "").trim()))]
      .sort((a,b)=>a.localeCompare(b,"id",{numeric:true,sensitivity:"base"}));
    return `<div class="publication-filter-field">
      <label id="publicationFilterLabel${index}">${escapeHtml(label)}</label>
      <details class="publication-multiselect" data-column="${index}">
        <summary aria-labelledby="publicationFilterLabel${index}">
          <span class="publication-filter-summary" data-summary>Semua ${escapeHtml(label)}</span>
          <span class="publication-filter-chevron" aria-hidden="true">⌄</span>
        </summary>
        <div class="publication-multiselect-panel">
          <input type="search" class="publication-option-search" placeholder="Cari pilihan..." aria-label="Cari pilihan ${escapeHtml(label)}">
          <div class="publication-option-actions">
            <button type="button" data-select-all>Pilih Semua</button>
            <button type="button" data-clear-all>Hapus Pilihan</button>
          </div>
          <div class="publication-option-list" role="group" aria-label="Pilihan ${escapeHtml(label)}">
            ${values.map((value,i) => `<label class="publication-option">
              <input type="checkbox" value="${escapeHtml(value)}" data-option>
              <span title="${escapeHtml(value||"(Kosong)")}">${escapeHtml(value||"(Kosong)")}</span>
            </label>`).join("")}
          </div>
          <p class="publication-option-help">Tanpa pilihan = semua data. Pilih beberapa nilai untuk menggabungkan hasil.</p>
        </div>
      </details>
    </div>`;
  }).join("");
  const multiselects = Array.from(filterGrid.querySelectorAll(".publication-multiselect"));
  function selectedValues(group) {
    return new Set(Array.from(group.querySelectorAll("input[data-option]:checked"), box => box.value));
  }
  function refreshFilterSummary(group) {
    const selected = selectedValues(group);
    const label = group.closest(".publication-filter-field").querySelector("label").textContent;
    const count = group.querySelectorAll("input[data-option]").length;
    const summary = group.querySelector("[data-summary]");
    summary.textContent = !selected.size || selected.size === count
      ? `Semua ${label}`
      : `${selected.size} dipilih · ${label}`;
    group.classList.toggle("has-selection",selected.size>0 && selected.size<count);
  }
  function getFiltered() {
    const q = search.value.trim().toLocaleLowerCase("id-ID");
    return rows.filter(row => {
      const matchesSearch = !q || row.some(value => String(value ?? "").toLocaleLowerCase("id-ID").includes(q));
      const matchesFilters = multiselects.every(group => {
        const selected=selectedValues(group);
        const total=group.querySelectorAll("input[data-option]").length;
        return !selected.size || selected.size===total ||
          selected.has(String(row[Number(group.dataset.column)] ?? "").trim());
      });
      return matchesSearch && matchesFilters;
    });
  }
  function render() {
    const current = getFiltered();
    document.getElementById("publicationRows").innerHTML = current.map(row => {
      const originalIndex=allRows.indexOf(row);
      return `<tr>${visiblePublicationIndices.map(i => `<td class="${i===8 || i===14 ? 'num' : ''}">${escapeHtml(row[i]??"")}</td>`).join('')}${adminMode?`<td><div class="admin-table-buttons"><button type="button" data-edit-pub="${originalIndex}">Edit</button><button type="button" class="remove" data-delete-pub="${originalIndex}">Hapus</button></div></td>`:""}</tr>`;
    }
    ).join('') || `<tr><td colspan="${headers.length+(adminMode?1:0)}" class="center">${rows.length ? 'Publikasi tidak ditemukan.' : 'Belum ada data publikasi.'}</td></tr>`;
    document.getElementById("publicationCount").textContent = `Menampilkan ${current.length} dari ${rows.length} publikasi`;
  }
  document.getElementById("backToFaculty").addEventListener("click", ()=>showFacultyTable(dept));
  if(adminMode) {
    document.getElementById("addPublication").addEventListener("click",()=>openRowEditor("publication",dept,null,code));
    document.getElementById("publicationRows").addEventListener("click",e=>{
      const edit=e.target.closest("[data-edit-pub]"), del=e.target.closest("[data-delete-pub]");
      if(edit) openRowEditor("publication",dept,Number(edit.dataset.editPub),code);
      if(del) deleteRow("publication",dept,Number(del.dataset.deletePub),code);
    });
  }
  document.getElementById("exportPublications").addEventListener("click", () => exportCSV(headers, getFiltered().map(row=>visiblePublicationIndices.map(i=>row[i]??"")), `publikasi_${code}_${yearFilter.value}.csv`));
  search.addEventListener("input", render);
  multiselects.forEach(group=>{
    const checkboxes=Array.from(group.querySelectorAll("input[data-option]"));
    const optionSearch=group.querySelector(".publication-option-search");
    group.addEventListener("toggle",()=>{
      if(group.open) multiselects.forEach(other=>{if(other!==group)other.open=false;});
    });
    checkboxes.forEach(box=>box.addEventListener("change",()=>{
      refreshFilterSummary(group);
      render();
    }));
    optionSearch.addEventListener("input",()=>{
      const term=optionSearch.value.trim().toLocaleLowerCase("id-ID");
      group.querySelectorAll(".publication-option").forEach(label=>{
        label.hidden=!label.textContent.toLocaleLowerCase("id-ID").includes(term);
      });
    });
    group.querySelector("[data-select-all]").addEventListener("click",()=>{
      checkboxes.forEach(box=>{if(!box.closest(".publication-option").hidden)box.checked=true;});
      refreshFilterSummary(group);
      render();
    });
    group.querySelector("[data-clear-all]").addEventListener("click",()=>{
      checkboxes.forEach(box=>box.checked=false);
      refreshFilterSummary(group);
      render();
    });
    refreshFilterSummary(group);
  });
  document.addEventListener("click",function closePublicationFilters(event){
    if(!filterGrid.isConnected){
      document.removeEventListener("click",closePublicationFilters);
      return;
    }
    if(!event.target.closest(".publication-multiselect")) {
      multiselects.forEach(group=>group.open=false);
    }
  });
  document.getElementById("resetPublicationFilters").addEventListener("click",()=>{
    multiselects.forEach(group=>{
      group.querySelectorAll("input[data-option]").forEach(box=>box.checked=false);
      const optionSearch=group.querySelector(".publication-option-search");
      optionSearch.value="";
      group.querySelectorAll(".publication-option").forEach(label=>label.hidden=false);
      group.open=false;
      refreshFilterSummary(group);
    });
    search.value="";
    render();
  });
  render();
  modal.classList.add("open");
}

function showDetails(item) {
  showFacultyTable(item.dept, item.displayName || item.dept); return;
  modalTitle.textContent = `Detail ${item.dept} — ${item.group === "FM" ? "Publikasi FM" : "Publikasi Mahasiswa"}`;
  modalBody.innerHTML = `
    <p>Ringkasan indikator yang ditampilkan pada grafik ${item.dept}. Angka di bawah mengikuti data contoh pada dashboard sumber dan dapat disesuaikan dengan data resmi.</p>
    <div class="modal-stats">
      <div class="stat"><small>Target</small><strong>${item.target}</strong></div>
      <div class="stat"><small>Realisasi</small><strong>${item.actual}</strong></div>
      <div class="stat"><small>Score</small><strong>${item.score}</strong></div>
    </div>
    <ul>
      <li><b>Jumlah FM:</b> ${item.fmCount.replace("Jumlah FM: ", "")}</li>
      <li><b>FM yang memiliki Scopus:</b> ${item.fmPercent}% (5 dari 10 pada tampilan contoh).</li>
      <li><b>Paper Scopus:</b> bobot ${item.papers}; tercantum 14 judul.</li>
      <li><b>Jurnal Scopus:</b> bobot ${item.journals}; tercantum 14 judul.</li>
      <li><b>Konferensi Scopus:</b> bobot ${item.conferences}; tercantum 14 judul.</li>
      <li><b>Book Chapter:</b> bobot ${item.bookChapter}; tercantum 14 judul.</li>
      <li><b>Full Book:</b> bobot ${item.fullBook}; tercantum 14 judul.</li>
    </ul>
    <p><b>Catatan:</b> pastikan nilai bobot, jumlah judul, dan persentase diverifikasi sebelum dashboard dipakai sebagai laporan resmi.</p>`;
  modal.classList.add("open");
}

function closeModal() { modal.classList.remove("open"); }

document.getElementById("toggleAdmin").addEventListener("click",()=>{
  adminMode=!adminMode;
  document.getElementById("adminPanel").classList.toggle("hidden-admin",!adminMode);
  document.getElementById("toggleAdmin").classList.toggle("active",adminMode);
  document.getElementById("toggleAdmin").textContent=adminMode?"✓ Selesai Mengelola":"⚙ Kelola Data";
  renderCards();
});
document.getElementById("createDataset").addEventListener("click",()=>{
  currentDataset(true); persist(); renderCards();
});
document.getElementById("saveSummary").addEventListener("click",()=>{
  const data=currentDataset(true);
  const definitions=[...Array.from({length:4},(_,i)=>[`title${i}`,`Title ${i}`]),...summaryIds.map(id=>[id,id])];
  const values=readInputFields(document.getElementById("summaryEditor"),definitions);
  data.titles=values.slice(0,4); data.summary=values.slice(4);
  persist();renderCards();
});
document.getElementById("saveQuarterSummary").addEventListener("click",()=>{
  const q=quarterFilter.value;
  if(q==="all") {showStatus("Pilih Q1, Q2, Q3, atau Q4 terlebih dahulu.",true);return;}
  const data=currentDataset(true);
  data.quarterSummaries ||= {};
  data.quarterSummaries[q]=Array.from(document.querySelectorAll('#quarterSummaryEditor input'),input=>input.value.trim());
  persist();renderCards();
});
document.getElementById("resetSummaryTitles").addEventListener("click",()=>{
  const data=currentDataset(true);
  data.titles=deepCopy(automaticTitles());
  persist();
  renderCards();
});
document.getElementById("cardSelector").addEventListener("change",e=>{
  selectedCardId=e.target.value; renderCardEditor();
});
document.getElementById("addCard").addEventListener("click",()=>{
  const data=currentDataset();if(!data) return;
  const id=generateId();
  data.cards.push({id,dept:"EBC",displayName:"EBC",group:isPublicationIndicator() ? activePublicationGroup() : "GENERAL",quarter:quarterFilter.value==="all" ? "" : quarterFilter.value,score:"0",fmCount:isPublicationIndicator() ? "Jumlah FM: 0 FM" : "",
    target:0,actual:0,fmPercent:0,papers:0,journals:0,conferences:0,bookChapter:0,fullBook:0,
    fmScopusCount:0,fmTotal:0,paperTitles:0,journalTitles:0,conferenceTitles:0,chapterTitles:0,bookTitles:0});
  selectedCardId=id;persist();renderCards();
});
document.getElementById("saveCard").addEventListener("click",()=>{
  const data=currentDataset();if(!data) return;
  const card=data.cards.find(c=>c.id===selectedCardId);if(!card) return;
  const values=readInputFields(document.getElementById("cardEditor"),cardFields);
  cardFields.forEach(([key],i)=>{
    card[key]=["target","actual","fmPercent","papers","journals","conferences","bookChapter","fullBook",
      "fmScopusCount","fmTotal","paperTitles","journalTitles","conferenceTitles","chapterTitles","bookTitles"].includes(key)
      ? safeNumber(values[i]) : values[i];
  });
  persist();renderCards();
});
document.getElementById("deleteCard").addEventListener("click",()=>{
  const data=currentDataset();if(!data || !selectedCardId) return;
  removeCardById(selectedCardId);
});
document.getElementById("exportBackup").addEventListener("click",()=>{
  downloadFile(`backup_dashboard_${new Date().toISOString().slice(0,10)}.json`,
    JSON.stringify(appData,null,2),"application/json;charset=utf-8");
  showStatus("Backup JSON berhasil diunduh.");
});
document.getElementById("importBackup").addEventListener("click",()=>document.getElementById("backupFile").click());
document.getElementById("backupFile").addEventListener("change",async e=>{
  const file=e.target.files[0];if(!file) return;
  try {
    const imported=JSON.parse(await file.text());
    if(!imported || !imported.years || typeof imported.years!=="object" || Array.isArray(imported.years)) throw Error("Format file tidak sesuai");
    for(const groups of Object.values(imported.years)) {
      if(!groups || typeof groups!=="object") throw Error("Data tahun tidak valid");
      for(const data of Object.values(groups)) {
        if(!Array.isArray(data.cards) || !Array.isArray(data.summary) ||
          !data.facultyByDept || !data.publicationsByDept) throw Error("Format dataset tidak valid");
      }
    }
    if(!confirm("Import akan mengganti semua data yang tersimpan di browser ini. Lanjutkan?")) return;
    appData=imported;persist();renderCards();
    showStatus("Data berhasil diimpor. Semua tampilan telah diperbarui.");
  } catch(err) { showStatus("Import gagal: "+err.message,true); }
  finally {e.target.value="";}
});
document.getElementById("resetDataset").addEventListener("click",()=>{
  if(!confirm(`Reset data ${yearFilter.value} / ${dataKey()}? Data tersimpan untuk kombinasi ini akan dihapus.`))return;
  if(yearFilter.value==="2026" && dataKey()==="publication") {
    appData.years["2026"].publication=deepCopy(initialData.years["2026"].publication);
  } else if(appData.years[yearFilter.value]) {
    delete appData.years[yearFilter.value][dataKey()];
  }
  selectedCardId="";persist();renderCards();
});
document.getElementById("updateDate").addEventListener("change",e=>{
  appData.updated=e.target.value;persist();
});
yearFilter.addEventListener("change",()=>{selectedCardId="";renderCards();});
yearChoices.forEach(input=>input.addEventListener("change",()=>{
  const years=yearChoices.filter(box=>box.checked).map(box=>box.value);
  if(!years.length) {input.checked=true;return;}
  updateYearSelection(years);
}));
document.getElementById("yearSelectAll").addEventListener("click",()=>updateYearSelection(["2023","2024","2025","2026"]));
document.getElementById("yearReset").addEventListener("click",()=>updateYearSelection(["2026"]));
document.addEventListener("click",event=>{if(!yearMultiFilter.contains(event.target))yearMultiFilter.open=false;});
indicatorFilter.addEventListener("change", renderCards);
quarterFilter.addEventListener("change", renderCards);
document.getElementById("closeModal").addEventListener("click", closeModal);
modal.addEventListener("click", e => { if (e.target === modal) closeModal(); });
document.addEventListener("keydown", e => { if (e.key === "Escape") closeModal(); });
document.getElementById("resetButton").addEventListener("click", () => {
  yearFilter.value = "2026";
  updateYearSelection(["2026"]);
  yearMultiFilter.open=false;
  indicatorFilter.value = "fm";

  renderCards();
});
updateYearMultiLabel();
renderCards();
