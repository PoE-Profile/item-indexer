<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>demo.poe-profile.info</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="screen" title="no title">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="{{ URL::to('/') }}/jquery/chosen.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/sockets.css">

  <style type="text/css">
        /* always present */
    .expand-transition {
      transition: all .3s ease;
      height: 30px;
      padding: 10px;
      background-color: #eee;
      overflow: hidden;
    }
    /* .expand-enter defines the starting state for entering */
    /* .expand-leave defines the ending state for leaving */
    .expand-enter, .expand-leave {
      height: 0;
      padding: 0 10px;
      opacity: 0;
    }
  </style>
  </head>
  <body>
    <div class="container-fluid">
        <nav class="navbar navbar-default navbar-fixed-top">
          <div class="navbar-header">

              <!-- Collapsed Hamburger -->
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                  <span class="sr-only">Toggle Navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>

              <!-- Branding Image -->
              <a class="navbar-brand" href="{{ url('/') }}">
                  demo.poe-profile.info
              </a>
          </div>

          <div class="collapse navbar-collapse" id="app-navbar-collapse">

              <!-- Right Side Of Navbar -->
              <ul class="nav navbar-nav navbar-right" style="padding-right: 10px;">
                <li class="nav-item float-lg-right"><a href="https://github.com/PoE-Profile/item-indexer">
                    <i aria-hidden="true" class="fa fa-github fa-4" style="font-size: 25px;"></i>Github</a>
                </li>
              </ul>
          </div>
        </nav>
      <div id="app" style="height: 800px;">
        <div class="alert alert-warning" role="alert">This is Demo of <a href="https://github.com/PoE-Profile/item-indexer"><i aria-hidden="true" class="fa fa-github fa-4" style="font-size: 25px;"></i> https://github.com/PoE-Profile/item-indexer</a> it has around 500 000 items indexed on 17.03.2018</div>
        @yield('content')
      </div>
    </div>


    @yield('jsData')
    <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>

    <script type="text/javascript" src="{{ URL::to('/') }}/jquery/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="{{ URL::to('/') }}/jquery/chosen.proto.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

  @yield('script')
  </body>
</html>
