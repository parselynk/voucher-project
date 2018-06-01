@extends('master')
@section('content')
<!-- /.row -->
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart-o fa-fw"></i> Generate vouchers
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <form class="form-inline col-md-12" method="POST" action="/createvouchers">
                        {{ csrf_field() }}
                        <select id="offer-select" class="form-control mb-3 mr-3" name="offer-id">
                    		<option value>Select an offer</option>
                                @if(isset($offers) && $offers->count() > 0 )
                                    @foreach($offers as $offer)
                                        <option value={{ $offer->id }} > {{ $offer->name }} </option>
                                    @endforeach
                                @endif
                		</select>
	                    <label for="expiry-date" class="mb-3 ml-sm-3" >Expiry date:</label>
	                    <input id="expiry-date" name="expiry-date"  class="form-control mb-3 mr-sm-3 datepicker " value = "">
        	<button type="submit" class="btn btn-primary mb-3">Generate</button>
    		</form>
            @if(count($errors))
                <hr />
                <div class="col-lg-12 col-md-12 mt-5">
                    <div class="alert alert-danger" role="alert">
                        @foreach($errors->all() as $error) 
                            <ul>
                                <li>{{ $error }}</li>
                            </ul>
                        @endforeach
                    </div>
                </div>
            @endif
            </div>
            <!-- /.panel-body -->
        </div>
    </div>
    <!-- /.col-lg-8 -->
</div>
<!-- /.row -->
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Vouchers table
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th align="center">Code</th>
                            <th align="center">Used</th>
                            <th align="center">Recipient Email</th>
                            <th align="center">Offer</th>
                            <th align="center">Expiry date</th>
                            <th align="center">Used at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($vouchers) && $vouchers->count() > 0 )
                            @foreach($vouchers as $voucher)
                                <tr class="odd gradeX">
                                    <td align="center">{{ $voucher->code }}</td>
                                    <td align="center">{{ !is_null($voucher->used_at) ? "Yes" : "No" }}</td>
                                    <td align="center">{{ $voucher->user->email }}</td>
                                    <td align="center">{{ $voucher->offer->name }}</td>
                                    <td class="center">{{  \Carbon\Carbon::parse($voucher->expire_at)->toDateString() }}</td>
                                    <td class="center">{{ !is_null($voucher->used_at) ? $voucher->used_at : "NA" }}</td>
                                </tr>
                            @endforeach
                          @else
                            <tr class="odd gradeX">
                                <td align="center">NA</td>
                                <td align="center">NA</td>
                                <td align="center">NA</td>
                                <td class="center">NA</td>
                                <td class="center">NA</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
@endsection