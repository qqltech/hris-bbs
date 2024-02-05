@php
  $req = app()->request;
  $tipe = $req->tipe_report;

  $rekap = [];
 
  
@endphp
<span style="font-weight:bold; font-size: 10pt">{{$tipe}}</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> HK : Hari Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> H : Masuk Kerja</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> I : Ijin</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> A : Tidak Masuk Kerja / Tanpa Keterangan</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CI AVG : Rata-rata Jam Checkin</span><br/>
<span style="font-weight:bold; font-style: italic; font-size: 8pt"> CO AVG : Rata-rata Jam Checkout</span><br/>
<br/>
<table v-else class="table-auto w-full" cellpadding="3">
  <thead class="bg-[#c6c6c6]">
    <tr style="text-align: center;">
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6; text-align: center; width: 3%;" rowspan="2">
        <div style="vertical-align: middle;">No
        </div>
        </th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Senin 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Selasa 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Rabu 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Kamis 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Jumat 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Sabtu 08:00 - 17.00</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;" colspan="2">Minggu 08:00 - 17.00</th>
    </tr>
    <tr style="text-align: center;">
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">NIK</th>
      <th style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; background-color: #c6c6c6;">Nama</th>
    </tr>
  </thead>
  <tbody>
        <tr>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse; width: 3%;">1</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">3578133394923</td>
          <td style="border:0.5px solid black; padding: 2px; font-size: 6.5pt; border-collapse: collapse;">Arjuna Radja Samudra Hindia</td>
        </tr>
  </tbody>
</table>
