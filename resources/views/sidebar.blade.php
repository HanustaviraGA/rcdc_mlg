<nav id="sidebar">
   <div class="sidebar_blog_2">
      <h4>RCDC@Malang</h4>
      <ul class="list-unstyled components">
         <li class="active"><a href="{{ route('home.index') }}"><span>Home</span></a></li>
         <li><a href="{{ route('dosen.index') }}"><span>Dosen</span></a></li>
         <li>
            <a href="#element" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><span>Report</span></a>
            <ul class="collapse list-unstyled" id="element">
               <li><a href="{{ route('perhitungankpi.index') }}">> <span>Perhitungan KPI</span></a></li>
            </ul>
         </li>
      </ul>
   </div>
</nav>