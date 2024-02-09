@php
  $req = app()->request;
  $tipe = $req->tipe_report;

  $rekap = [];
  $periode = $req->periode.'-01';
  $data = \DB::select("
     select * from employee_attendance_detail(?,?)
    ",[ $periode, $req->kary_id ]);
  
@endphp
<span style="font-weight:bold; font-size: 10pt"> Absensi Karyawan Detail</span><br/>
<span style="font-weight:bold; font-size: 9pt"> {{ @json_decode(@$data[0]->kary)->nik }} - {{ @json_decode(@$data[0]->kary)->nama_lengkap }}</span><br/>
<span style="font-weight:bold; font-size: 9pt"> Periode  {{$req->periode}}</span><br/></br>
<br/>
    <table v-else class="table-auto w-full" cellpadding="3">
      <thead class="bg-[#c6c6c6]">
        <tr>
          <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 15%;">Tanggal</th>
          <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 12%;">Tipe Hari</th>
          <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 12%; text-align: center;">Status</th>
          <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkin Time</th>
          <th style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; background-color: #c6c6c6; width: 10%; text-align: center;">Checkout Time</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $dt)
            <tr>
              <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 15%;">{{ $dt->day_name_idn }}, {{$dt->date_to_idn}}</td>
              <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 12%;">{{ $dt->type }}</td>
              <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 12%; text-align: center;">{{ @json_decode($dt->absensi)->status }}</td>
              <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 10%; text-align: center;">{{ @json_decode($dt->absensi)->checkin_time }}</td>
              <td style="border:0.5px solid black; padding: 2px; font-size: 9pt; border-collapse: collapse; width: 10%; text-align: center;">{{ @json_decode($dt->absensi)->checkout_time }}</td>
            </tr>
        @endforeach
      </tbody>
    </table>