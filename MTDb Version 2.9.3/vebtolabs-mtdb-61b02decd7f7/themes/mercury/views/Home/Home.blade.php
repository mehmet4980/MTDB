@extends('Main.Boilerplate')

@section('assets')
  @parent
  {{ HTML::style('themes/mercury/assets/css/slider-single.css') }}
@stop

@section('bodytag')
	<body id="home" class="nav-trans animate-nav">
@stop

@section('nav')
	@include('Partials.Navbar')
@stop

@section('content')
 	{{ $content }}
@stop

@section('scripts')

	{{ HTML::script('assets/js/slick.min.js') }}

	<script>
        $('.titles-carousel').slick({
            infinite: true,
            slidesToShow: 6,
            slidesToScroll: 6,
            responsive: [
                {
                    breakpoint: 1480,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4
                    }
                },
                {
                    breakpoint: 1230,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '40px',
                        slidesToShow: 1
                    }
                }
            ]
        });

        $('.news-item img').lazyload();

        $('.navbar').affix({
            offset: {
                top: 600
            }
        });

        vars.trailersPlayer = '<?php echo $options->trailersPlayer(); ?>';
        ko.applyBindings(app.viewModels.home, $('.content')[0]);
    </script>

@stop