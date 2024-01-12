@php
$req = app()->request;

$raw = \DB::select("
select
k.nik, k.jk_id, k.nama_lengkap, kd.nama dir, kdi.nama divisi, kde.nama dept, fd.total_gaji, fd.total_tax, fd.netto,
fd.periode_in_date , fd.periode,f.created_at, f.desc
from t_final_gaji_det fd
join t_final_gaji f on fd.t_final_gaji_id = f.id
join m_kary k on k.id = fd.m_kary_id
left join m_dir kd on kd.id = fd.m_kary_dir_id
left join m_divisi kdi on kdi.id = fd.m_kary_divisi_id
left join m_dept kde on kde.id = fd.m_kary_dept_id
where f.status = 'POSTED'
");

$totalGaji = 0;

$rekeningFrom = \DB::select("select g.value from m_general g where g.group = 'REKENING TRANSFER' limit 1");

// Loop melalui hasil query dan menjumlahkan total_gaji
foreach ($raw as $row) {
    $totalGaji += $row->netto;
}
@endphp
  <table width="100%" style="font-size:8px;" cellpadding="1">
    <thead style="font-weight:semibold;">
            <tr style="">
        <td
          style=" font-weight: bold; line-height: 20px;text-align:center "
          >
          P</td>
        <td
          style=" font-weight: bold; line-height: 20px;text-align:center; "
          >
          {{ $raw[0]->created_at}}</td>
        <td
          style=" font-weight: bold; line-height: 20px;text-align:center; "
          >
          {{ $rekeningFrom[0]->value }} </td>
        <td
          style=" font-weight: bold; line-height: 20px;text-align:center; "
          >
          {{count($raw)}}</td>
        <td
          style=" font-weight: bold; line-height: 20px;text-align:center; "
          >
          {{$totalGaji}}</td>
      </tr>
      @foreach($raw as $i => $d)      
        <tr style="">
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            {{$d->nik}}</td>

          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            {{ $d->nama_lengkap}}</td>
            <td></td>
            <td></td>
            <td></td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            IDR</td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            {{ $d->netto}}</td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            {{$d->desc}}</td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
            >
            {{ ($d->jk_id === 346) ? 'IBU' : 'BPK'}}</td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
          >MANDIRI</td>
          <td
            style=" font-weight: bold; line-height: 20px;text-align:center; "
          >MADIUN</td>
        </tr>
      @endforeach
    </thead>
  </table>