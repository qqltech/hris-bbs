@php
use App\Models\CustomModels\m_kary;

$req = app()->request;

if ($req->filled('kary_id')) {
    $ids = array_map('intval', explode(',', $req->kary_id));
} else {
    $ids = m_kary::query()
        ->when($req->filled('m_divisi_id'), function ($q) use ($req) {
            $q->where('m_divisi_id', $req->m_divisi_id);
        })
        ->when($req->filled('m_dept_id'), function ($q) use ($req) {
            $q->where('m_dept_id', $req->m_dept_id);
        })
        ->pluck('id')
        ->toArray();
}
@endphp

@foreach($ids as $kary_id)
<span style="font-weight:bold; font-size: 10pt"> Absensi Karyawan Detail</span><br/>
@php
  $rekap = [];
  //$periode = $req->periode.'-'.date('d');

  $date_start = Carbon\Carbon::parse($req->date_start)->format('Y-m-d');
  $date_end = Carbon\Carbon::parse($req->date_end)->format('Y-m-d');
  $dateNow = date('Y-m-d');
  
  $data = \DB::select("
     select * from employee_attendance_detail_range(?,?,?)
    ",[ $date_start, $date_end, $kary_id]);
  
  $check_kary_jam_kerja_tipe = \DB::table('m_kary as k')->join('m_general as g','g.id','k.tipe_jam_kerja_id')
    ->where('k.id', $kary_id)->pluck('g.code')->first();
  
  $rekap = \DB::select("
    select 
      employee_attendance_range(?,?,k.id) absen,
      (select   
        TO_CHAR(INTERVAL '1 second' * AVG(EXTRACT(EPOCH FROM pa.checkin_time::TIME)), 'HH24:MI:SS')
        from presensi_absensi pa where pa.default_user_id = u.id and pa.checkin_time is not null and to_char(pa.tanggal,'mm') = '11')  checkin_avg,
      (select   
        TO_CHAR(INTERVAL '1 second' * AVG(EXTRACT(EPOCH FROM pa.checkout_time::TIME)), 'HH24:MI:SS')
        from presensi_absensi pa where pa.default_user_id = u.id and pa.checkout_time is not null and to_char(pa.tanggal,'mm') = '11') checkout_avg,
      k.id, kode, nama_lengkap, d.nama dept 
    from m_kary k
    join default_users u on u.m_kary_id = k.id
    join m_dept d on d.id = k.m_dept_id
        where k.is_active = true 
        and k.m_dept_id IS NOT NULL and k.m_dept_id != 0
        and k.id = COALESCE(?, k.id)
        ",[ $date_start, $date_end, $kary_id ]);

  $total_checkin_telat = 0;
  $total_checkout_lebih_awal = 0;
  $total_checkin_lebih_awal = 0;
  $total_checkout_telat = 0;
  
@endphp
<span style="font-weight:bold; font-size: 7pt"> {{ @json_decode(@$data[0]->kary)->nik }} - {{ @json_decode(@$data[0]->kary)->nama_lengkap }}</span><br/>
<span style="font-weight:bold; font-size: 7pt"> Periode  {{$date_start}} - {{$date_end}}</span><br/></br></br>
<table style="width: 100%; font-size: 7pt" cellpadding="2">
  <thead class="bg-[#c6c6c6]">
    <tr>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 15%;">Tanggal</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 6%;">Tipe Hari</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 8%; text-align: center;">Status</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkin Time</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkin Scope</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkout Time</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkout Scope</th>

    </tr>
  </thead>
  <tbody>
    @foreach($data as $dt)
        @php
          if(strtolower($check_kary_jam_kerja_tipe) == 'office'){
            $jadwal = \DB::table('t_jadwal_kerja as t')
              ->join('t_jadwal_kerja_det_hari as h','h.t_jadwal_kerja_id','t.id')
              ->where('t.status','POSTED')->whereRaw("lower(h.day) = ?", [strtolower($dt->day_name_idn)])
              ->first();
          }else{
            $jadwal = \DB::table('t_jadwal_kerja as t')
              ->join('t_jadwal_kerja_det_hari as h','h.t_jadwal_kerja_id','t.id')
              ->join('t_jadwal_kerja_det as d','d.t_jadwal_kerja_det_hari_id','h.id')
              ->where('t.status','POSTED')
              ->whereRaw("lower(h.day) = ? and d.m_kary_id = ?", [ strtolower($dt->day_name_idn) , $kary_id ])
              ->first();
          }
          $waktu_mulai = strtotime(@$jadwal->waktu_mulai);
          $waktu_checkin = strtotime(@json_decode($dt->absensi)->checkin_time);


          // HITUNG TELAT CHECKIN
          $checkin_result = @json_decode($dt->absensi)->checkin_time;
          if(@json_decode($dt->absensi)->checkin_time != null && @$jadwal->waktu_mulai){
            // Menghitung selisih waktu dalam detik
            $selisih_detik = $waktu_mulai - $waktu_checkin;

            // Mengonversi selisih detik menjadi menit
            $late = abs(round($selisih_detik / 60));
            
            if(($waktu_mulai < $waktu_checkin)){
              $checkin_result = @json_decode($dt->absensi)->checkin_time . ' / '.@$jadwal->waktu_mulai.'  <span style="color: red">('.$late.' Menit )</span>'; 
              $total_checkin_telat += $late;
            }else{
              $checkin_result = @json_decode($dt->absensi)->checkin_time .(@$jadwal->waktu_mulai ?  ' / '. @$jadwal->waktu_mulai : '');
              $total_checkin_lebih_awal += $late;
            }
          }

          // HITUNG TELAT CHECKOUT
          $checkout_result = @json_decode($dt->absensi)->checkout_time;
          $waktu_akhir = strtotime(@$jadwal->waktu_akhir);
          $waktu_checkout = strtotime(@json_decode($dt->absensi)->checkout_time);

          if(@json_decode($dt->absensi)->checkout_time != null && @$jadwal->waktu_akhir){
            // Menghitung selisih waktu dalam detik
            $selisih_detik = $waktu_akhir - $waktu_checkout;

            // Mengonversi selisih detik menjadi menit
            $late = abs(round($selisih_detik / 60));

            if(($waktu_akhir > $waktu_checkout)){
              $checkout_result = @json_decode($dt->absensi)->checkout_time . ' / '.@$jadwal->waktu_akhir.'  <span style="color: red">('.$late.' Menit )</span>'; 
              $total_checkout_lebih_awal += $late;
            }else{
              $total_checkout_telat += $late;
              $checkout_result = @json_decode($dt->absensi)->checkout_time .(@$jadwal->waktu_akhir ?  ' / '. @$jadwal->waktu_akhir : '');
            }
          }
          $absensi = json_decode($dt->absensi, true);
          //dd($absensi);

         $status = $absensi['status'] ?? '-';
        $catatanIn = $absensi['catatan_in'] ?? '-';
        $catatanOut = $absensi['catatan_out'] ?? '-';

        $checkinScope = !is_null($absensi['checkin_on_scope'])
            ? ($absensi['checkin_on_scope'] ? 'IN SCOPE' : 'OUT SCOPE')
            : '-';

        $checkoutScope = !is_null($absensi['checkout_on_scope'])
            ? ($absensi['checkout_on_scope'] ? 'IN SCOPE' : 'OUT SCOPE')
            : '-';

          // setelah hitung $checkin_result / $checkout_result

        // cek checkin_result dulu
        $checkin_info  = '';
        $checkout_info = '';

        // … proses hitung checkin_result / checkout_result seperti biasa …

        // bikin variabel info scope+catatan terpisah
        if (!empty($checkin_result)) {
            $scopeText  = $checkinScope ?: '';
            $catatanText = $catatanIn ? ' | Catatan: '.$catatanIn : '';
            $checkin_info = '<small>'.$scopeText.$catatanText.'</small>';
        }

        if (!empty($checkout_result)) {
            $scopeText  = $checkoutScope ?: '';
            $catatanText = $catatanOut ? ' | Catatan: '.$catatanOut : '';
            $checkout_info = '<small>'.$scopeText.$catatanText.'</small>';
        }

        @endphp
        <tr>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 15%;">{{ $dt->day_name_idn }}, {{$dt->date_to_idn}}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 6%;">{{ $dt->type }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 8%; text-align: center;">{{ @json_decode($dt->absensi)->status }}</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkin_result !!}
          </td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkin_info !!}
          </td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkout_result !!}
          </td>
           <td style="border:0.5px solid black; padding: 2px; font-size: 7pt; border-collapse: collapse; width: 10%; text-align: center;">
            {!! $checkout_info !!}
          </td>
        </tr>
    @endforeach
  </tbody>
</table>
<br>
<table style="width: 100%; font-size: 7pt" >
  <tbody>
    <tr>
      <td style="color: red; width: 7%">Total Checkin Telat</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkin_telat/60).' Jam ('.$total_checkin_telat.' Menit)' }}</td>
      <td style="width: 7%">Hari Kerja</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ @json_decode(@$rekap[0]->absen)->work_days_in_month ?? '-' }}</td>
    </tr>
    <tr>
      <td style="color: red">Total Checkout Lebih Awal</td>
      <td style="width: 1%">:</td>
     <td style="width: 20%">{{ round($total_checkout_lebih_awal/60).' Jam ('.$total_checkout_lebih_awal.' Menit)' }}</td>
      <td>Hadir</td>
      <td style="width: 1%">:</td>
      <td>{{ @json_decode(@$rekap[0]->absen)->work_present ?? '-' }}</td>
    </tr>
    <tr>
      <td>Total Checkin Lebih Awal</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkin_lebih_awal/60).' Jam ('.$total_checkin_lebih_awal.' Menit)' }}</td>
      <td>Ijin / Cuti</td>
      <td style="width: 1%">:</td>
      <td>{{ @json_decode(@$rekap[0]->absen)->cuti_terpakai ?? '-' }}</td>
    </tr>
    <tr>
      <td>Total Checkout Telat</td>
      <td style="width: 1%">:</td>
      <td style="width: 20%">{{ round($total_checkout_telat/60).' Jam ('.$total_checkout_telat.' Menit)' }}</td>
      <td>Alpha</td>
      <td style="width: 1%">:</td>
      <td>{{ @json_decode(@$rekap[0]->absen)->work_not_present ?? '-' }}</td>
    </tr>
    <tr>
      <td>Rata-rata Jam Checkin</td>
      <td style="width: 1%">:</td>
      <td>{{ @$rekap[0]->checkin_avg ?? '-' }}</td>
    </tr>
    <tr>
      <td>Rata-rata Jam Checkout</td>
      <td style="width: 1%">:</td>
      <td>{{ @$rekap[0]->checkout_avg ?? '-' }}</td>
    </tr>
  </tbody>
</table>
@if(!$loop->last)
  <div style="page-break-after: always;"></div>
@endif
@endforeach