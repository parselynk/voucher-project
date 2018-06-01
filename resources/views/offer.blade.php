@extends('master')
@section('content')
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <!-- <i class="fa fa-bar-chart-o fa-fw"></i> --> &nbsp; Generate offer
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <form class="form-inline col-md-12 mb-3" method="POST" action="/createoffer">
                        {{ csrf_field() }}
                        <label for="name" class="mb-3 ml-sm-3" >Offer name:</label>
                        <div class="form-group input-group col-4">
                            <input id="name" type="text" class="form-control mb-3 mr-3" name="name">
                        </div>
                        <label for="expiry" class="mb-3 ml-sm-3" >Discount:</label>
                        <div class="form-group input-group">
                            <input id="discount" name="discount"  type="number" min="1" max="100" class="form-control">
                            <span class="input-group-addon">%</span>
                        </div>
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
                Offers table
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th align="center">Name</th>
                            <th align="center">Discount</th>
                            <th align="center">Total Vouchers</th>
                            <th align="center">Used vouchers</th>
                            <th align="center">Unused Vouchers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($offers) && $offers->count() >0 )
                            @foreach($offers as $offer)
                                <tr class="odd gradeX">
                                    <td align="center">{{ $offer->name }}</td>
                                    <td align="center">{{ $offer->discount * 100 }} %</td>
                                    <td align="center">{{ $offer->vouchers()->get()->count()}}</td>
                                    <td class="center">{{ $offer->vouchers()->whereNotNull('used_at')->count()}}</td>
                                    <td class="center">{{ $offer->vouchers()->whereNull('used_at')->count()}}</td>
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