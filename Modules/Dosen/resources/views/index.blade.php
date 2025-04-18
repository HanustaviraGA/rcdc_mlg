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
                                <h2>{!! config('dosen.name') !!}</h2>
                            </div>
                        </div>
                        <div class="full graph_revenue">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="tab_style2">
                                       <div class="tabbar padding_infor_info">
                                          <nav>
                                             <div class="nav nav-tabs" id="nav-tab1" role="tablist">
                                                <a class="nav-item nav-link active" id="nav-home-tab2" data-toggle="tab" href="#nav-home_s2" role="tab" aria-controls="nav-home_s2" aria-selected="true">Home</a>
                                                <a class="nav-item nav-link" id="nav-profile-tab2" data-toggle="tab" href="#nav-profile_s2" role="tab" aria-controls="nav-profile_s2" aria-selected="false">Profile</a>
                                                <a class="nav-item nav-link" id="nav-contact-tab2" data-toggle="tab" href="#nav-contact_s2" role="tab" aria-controls="nav-contacts_s2" aria-selected="false">Contact</a>
                                             </div>
                                          </nav>
                                          <div class="tab-content" id="nav-tabContent_2">
                                             <div class="tab-pane fade show active" id="nav-home_s2" role="tabpanel" aria-labelledby="nav-home-tab">
                                                
                                             </div>
                                             <div class="tab-pane fade" id="nav-profile_s2" role="tabpanel" aria-labelledby="nav-profile-tab">
                                                <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et 
                                                   quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos 
                                                   qui ratione voluptatem sequi nesciunt.
                                                </p>
                                             </div>
                                             <div class="tab-pane fade" id="nav-contact_s2" role="tabpanel" aria-labelledby="nav-contact-tab">
                                                <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et 
                                                   quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos 
                                                   qui ratione voluptatem sequi nesciunt.
                                                </p>
                                             </div>
                                          </div>
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
