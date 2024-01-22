@php
  $req = app()->request;
  $tipe = $req->tipe_report;

  $rekap = [];
  $periode_asli = $req->periode;
  $periode = $req->periode.'-01';
  $data = \DB::select("
    select 
      employee_attendance(?,k.id) absen,
      k.id, kode, k.tgl_masuk, nama_lengkap, dv.nama div, d.nama dept 
    from m_kary k
    join default_users u on u.m_kary_id = k.id
    join m_divisi dv on dv.id = k.m_divisi_id
    join m_dept d on d.id = k.m_dept_id
        where k.is_active = true 
        and k.m_dept_id IS NOT NULL and k.m_dept_id != 0
        and k.m_divisi_id = COALESCE(?, k.m_divisi_id) 
        and k.m_dept_id = COALESCE(?, k.m_dept_id)
        and k.id = COALESCE(?, k.id)
        ",[
          $periode, $req->m_divisi_id, $req->m_dept_id, $req->kary_id
        ]);
  
@endphp
<h4 style="font-weight:bold; font-size: 10pt">{{ $tipe . ' - Periode : '.$periode_asli}}</h4><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> LK     : Lama Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CMK     : Cuti Masa Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CMK (-) : Sisa Cuti Masa Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CTH     : Cuti Tahunan</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CTH (-) : Sisa Cuti Tahunan</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CP24    : P24</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CP24 (-) : Sisa P24</span><br/>
<br/>
<table v-else >
  <thead>
    <tr>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">Karyawan</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">Divisi</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">Departemen</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">Tgl Masuk</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">LK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CMK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CMK (-)</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CTH</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CTH (-)</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CP2</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6;">CP24 (-)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $dt)
        <tr>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->kode }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->nama_lengkap }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->div }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->dept }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse;">{{ $dt->tgl_masuk }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->masa_kerja ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->cuti_masa_kerja ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->sisa_cuti_masa_kerja ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->cuti_reguler ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->sisa_cuti_reguler ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->cuti_p24 ?? 0 }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; text-align: right">{{ @json_decode($dt->absen)->sisa_cuti_p24 ?? 0 }}</td>
        </tr>
    @endforeach
  </tbody>
</table>
