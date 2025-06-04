<?php

namespace App\Http\Controllers\FormInput;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\curlAPI;
use DateTime;
use Validator;
use App\Models\UserTtd;
use DB;
use PDF;
use Response;
use DataTables;
use \Carbon\Carbon;

class BarangController extends Controller
{

  public function getTipe() {
    // Report Pengajuan , Detail Pengajuan
      $tipe_code = ['87000','87001','87002','87003'];
      $data = UserReport::whereIn('cRptCode', $tipe_code)->where('crolesid', \Session::get('crolesid'))->get();

      if($data->isNotEmpty()) {
        return [
          'status' => 200, 
          'data' => $data
        ];
      } else {
        return [
          'status' => 300, 
          'message' => 'Tipe Report Tidak Ditemukan Pada User Ini !'
        ];
      }
  }

  public function loadArea()
  {
    if(!\Session::has('userToken')){
      \Session::flash('error', 'Waktu login anda habis, silahkan melakukan login ulang.');
      return redirect('logout');
    }

    if(!\Session::has('cLevelArea')){
      \Session::flash('error', 'Akun anda belum memiliki level akses area sales. Harap hubungi IT.');
      return redirect('/');
    }

    if(!empty(Session('cLevelArea'))) {
        switch(\Session::get('cLevelArea')){
          case "1":
            $this->type = [
              [
                "key" => "Area",
                "value" => "PER AREA"
              ]
            ];
            break;
          case "2":
            $this->type = [
              [
                "key" => "Area",
                "value" => "PER AREA"
              ],
              [
                "key" => "Regional",
                "value" => "PER REGION"
              ]
            ];
            break;
          case "3":
            $this->type = [
              [
                "key" => "Area",
                "value" => "PER AREA"
              ],
              [
                "key" => "Regional",
                "value" => "PER REGION"
              ],
              [
                "key" => "Nasional",
                "value" => "NASIONAL"
              ]
            ];
            break;
          case "4":
            $this->type = [
              [
                "key" => "Area",
                "value" => "PER AREA"
              ],
              [
                "key" => "Regional",
                "value" => "PER REGION"
              ]
            ];
            break;
        }
      } else {
        $this->type = [];
      }
      
      return $this->type;
  }

  public function inputView()
  {
    // if(!\Session::has('userToken')){
    //   \Session::flash('error', 'Waktu login anda habis, silahkan melakukan login ulang.');
    //   return redirect('logout');
    // }
  
    return view('form-input.barang.input')
          ->with('title', 'Report Marketing Global');
  }

  public function indexReport()
  {
    if(!\Session::has('userToken')){
      \Session::flash('error', 'Waktu login anda habis, silahkan melakukan login ulang.');
      return redirect('logout');
    }
  
    return view('pages.reports.report-global-marketing.report')
          ->with('cSEX3', true)
          ->with('title', 'Report Marketing Global')
          ->with('cMenu', 'sidebar-light-it')
          ->with('pSEX', true);
  }

  public function reportDatatable(Request $request) {

    $data_login = json_encode(array(
      "username" => Session('cuserId'),
      "password" => Session('password'))
    );

    $curl = new curlAPI;
    $responseToken = $curl->postDataWithBodyNoAuth('web/login', $data_login);
    $resToken = json_decode($responseToken);

    if(!empty($resToken->data[0]->token)){
      $token = $resToken->data[0]->token;
      \Session::put('tokenEvan', $token);
    } else {
      return [
          'status' => 203,
          'message' => 'Gagal Mendapatkan Token'
      ];
    }

    if($request->cTipe == '87000') {
      
      $tipe = 'sponsorshipJT';
      $data_post = json_encode(array(
        "tipe" => $tipe,
      ));

      $curl = new curlAPI;
      $data_sent = $curl->postDataWithBodyWithAuth('web/marketing/reportglobal', $data_post,\Session::get('tokenEvan'));
      $res = json_decode($data_sent);

      if($res && $res->status == 200) {
        return Datatables::of($res->data)
        ->make(true);
      } else {
        $data = [];
        return Datatables::of($data)
        ->make(true);
      } 

    } else if ($request->cTipe == '87001') {

      $tipe = 'endkontrakspoJT';
      $data_post = json_encode(array(
        "tipe" => $tipe,
      ));

      $curl = new curlAPI;
      $data_sent = $curl->postDataWithBodyWithAuth('web/marketing/reportglobal', $data_post,\Session::get('tokenEvan'));
      $res = json_decode($data_sent);

      if($res && $res->status == 200) {
        return Datatables::of($res->data)
        ->addColumn('target', function($row) {
          if (is_numeric($row->target)) {
            return '<td class="text-right">'.number_format($row->target, 0).'</td>';
          } else {
            return $row->target;
          }
        })
        ->rawColumns(['target'])
        ->make(true);
      } else {
        $data = [];
        return Datatables::of($data)
        ->make(true);
      } 

    } else if ($request->cTipe == '87002') {

      $data = DB::connection('sqlsrv')->select("exec d_webis.dbo.pr_rptKontrolPiutangSewaABJ");
      
      if($data) {
        return Datatables::of($data)
        ->make(true);
      } else {
        return Datatables::of([])
        ->make(true);
      } 
    } else if ($request->cTipe == '87003') {
      // dd($request->all());
      $start = Carbon::parse($request->startPeriode)->format('Ymd');
      $end = Carbon::parse($request->endPeriode)->format('Ymd');

      $data = DB::connection('sqlsrv')->select("SELECT a.cbranch , b.cDesc as cbranchdesc , a.cdocno , a.cdocref , a.cSuppcode as csuppcode , c.cNama as csuppname , a.dInputdate , a.cInputby , a.mBiayatotal , cTarget , a.cKeterangan from d_transaksi.dbo.t_msmst a (nolock)
      left join d_transaksi.dbo.t_branch b (nolock) on a.cbranch = b.cBranch
      left join d_transaksi.dbo.t_supplier c (nolock) on a.cSuppcode = c.csuppcode
      where a.cbranch='2000' and a.cSuppcode like '%$request->cSuppcode%' and CONVERT(char(8),a.dInputdate,112) BETWEEN '$start' and '$end'");

      if($data) {
        return Datatables::of($data)
        ->addColumn('action', function($row){
          return '<button class="btn btn-block btn-primary btn-xs text-white" type="button" id="btnReport" data-id="print"><i class="fas fa-print"></i> PRINT</button>
          ';
        })
        ->rawColumns(['action'])
        ->make(true);
      } else {
        return Datatables::of([])
        ->make(true);
      }
    }

    
  }

  public function printData($cbranch ,$cdocno ,$ctipe) {

    // $pdf = PDF::loadview('pages.reports.report-global-marketing.print',[])->setPaper('a4', 'landscape');
    // return $pdf->stream();

    $cbranch = trim(base64_decode($cbranch));
    $cdocno = trim(base64_decode($cdocno));
    $ctipe = trim(base64_decode($ctipe));

    $detail = DB::connection('sqlsrv')->select("exec d_webis.dbo.pr_rptCalculateROMI 'MS','$cbranch','$cdocno'");
    $detailPromosi = DB::connection('sqlsrv')->select("exec d_webis.dbo.pr_rptCalculateROMI 'ABJ','$cbranch','$cdocno'");

    // periode / 12 , tidak perlu round
    // subsidi dibawah 0 atau minus dijadikan 0 , alasannya agar rominya & cost ratio tidak minus

    $mst = DB::connection('sqlsrv')->select("SELECT a.cbranch , b.cDesc as cbranchdesc , a.cdocno , a.cdocref , a.cSuppcode as csuppcode , c.cNama as csuppname , a.dInputdate , a.cInputby , a.mBiayatotal , ISNULL(a.cTarget,0) as cTarget , a.cKeterangan , convert(varchar, a.dStartcycle, 23) as dStartcycle , convert(varchar, a.dEndcycle, 23) as dEndcycle , e.cCustcode , e.cNama as cCustname , ROUND(DATEDIFF(DAY, a.dStartcycle, a.dEndcycle) / 30.0 ,0) AS periodeKontrak , ROUND(
    DATEDIFF(
      DAY, 
      a.dStartcycle, 
      CASE 
        WHEN GETDATE() > a.dEndcycle THEN a.dEndcycle 
        ELSE GETDATE() 
      END
      ) / 30.0 , 0
    ) AS periodeBerjalan , a.cTarget / 
    ROUND(DATEDIFF(DAY, a.dStartcycle, a.dEndcycle) / 30.0 , 0 ) * 
    ROUND(
        DATEDIFF(
            DAY, 
            a.dStartcycle, 
            CASE 
                WHEN GETDATE() > a.dEndcycle THEN a.dEndcycle 
                ELSE GETDATE() 
            END
        ) / 30.0 , 0
    ) AS targetBerjalan from d_transaksi.dbo.t_msmst a (nolock)
    left join d_transaksi.dbo.t_branch b (nolock) on a.cbranch = b.cBranch
    left join d_transaksi.dbo.t_supplier c (nolock) on a.cSuppcode = c.csuppcode
    left join d_transaksi.dbo.t_suppliermapcustomer d (nolock) on c.csuppcode = d.cSuppcode
    left join d_transaksi.dbo.t_customer e (nolock) on d.cCustcode = e.cCustcode
    where a.cbranch='$cbranch' and cdocno='$cdocno'")[0];

    // dd($mst);

    $termin = DB::connection('sqlsrv')->select("SELECT * from d_transaksi.dbo.t_msschedule where cbranch='$cbranch' and cdocno='$cdocno'");

    $namaFile = 'Marketing Sponsorship Romi ('.$cdocno.').pdf';

    $pdf = PDF::loadview('pages.reports.report-global-marketing.print',
    ['mst' => $mst , 
    'detail' =>$detail, 
    'detailPromosi' =>$detailPromosi, 
    'termin' => $termin
    ])->setPaper('a4', 'landscape');
    return $pdf->stream($namaFile);

  }
  
}
