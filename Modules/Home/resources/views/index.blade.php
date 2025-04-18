@extends('master')

@section('konten')
    <div class="midde_cont">
        <div class="container-fluid">
            <!-- graph -->
            <div class="row column2 graph margin_bottom_30 mt-5">
                <div class="col-md-l2 col-lg-12">
                    <div class="white_shd full">
                        <div class="full graph_head">
                            <div class="heading1 margin_0">
                                <h2>Informasi</h2>
                            </div>
                        </div>
                        <div class="full graph_revenue">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="content">
                                        <div class="area_chart">
                                            <img src="{{ asset('images/custom/image001.png') }}" alt="Descriptive Alt Text" style="max-width: 100%; height: auto;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end graph -->
        </div>
    </div>
@endsection
