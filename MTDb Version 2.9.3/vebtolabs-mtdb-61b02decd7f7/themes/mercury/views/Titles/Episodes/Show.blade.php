@extends('Main.Boilerplate')

@section('title')
    <title> {{ $title->title.' '.trans_choice('main.season', 1).' '.$season->number.', '.trans('main.episode').' '.$episode->episode_number. ' - ' .$options->getSiteName() }}</title>
@stop

@section('meta')
    <meta name="title" content="{{ $title->title . ' - ' . $options->getSiteName() }}">
    <meta name="description" content="{{ $episode->plot ? $episode->plot : $title->plot }}">
    <meta name="keywords" content="{{ $options->getTitlePageKeywords() }}">
    <meta property="og:title" content="{{ $episode->title . ' - ' . $options->getSiteName() }}"/>
    <meta property="og:url" content="{{ Request::url() }}"/>
    <meta property="og:site_name" content="{{ $options->getSiteName() }}"/>
    <meta property="og:image" content="{{str_replace('w342', 'original', asset($episode->poster ? $episode->poster : $title->poster))}}"/>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="{{ $options->getSiteName() }}">
    <meta name="twitter:title" content="{{ $episode->title }}">
    <meta name="twitter:description" content="{{ $episode->plot ? $episode->plot : $title->plot }}">
    <meta name="twitter:image" content="{{ $episode->poster ? $episode->poster : $title->poster }}">
    <link rel="canonical" href="{{ Helpers::episodeUrl($episode->title, $episode->id, 'series', $season->number, $episode->episode_number) }}">
@stop

@section('bodytag')
  <body id="title-page" class="episode-page paper-theme">
@stop

@section('content')

    <div class="page-wrapper">
        <header class="header"><div class="overlay"></div></header>
        <div class="container" id="content">
        <div class="row">
            <div class="col-sm-9">
                <div class="row details-panel" id="ko-bind">
                    <div class="col-sm-3 poster">
                        <img src="{{$title->poster}}" alt="{{$title->title}}" class="img-responsive"/>
                        <button class="btn btn-block btn-primary" data-bind="click: showTrailerModal"><i class="fa fa-play"></i> Watch Trailer</button>
                    </div>
                    <div class="col-sm-9">
                        <h1>
                            <a href="{{ Helpers::url($title->title, $title->id, $title->type) }}">{{ $title->title }}</a>:
                            {{trans_choice('main.season', 1).' '.$season->number.', '.trans('main.episode').' '.$episode->episode_number }}
                        </h1>
                        <ul class="list-unstyled list-inline genres">
                            @foreach(explode('|', $title->genre) as $genre)
                                <li><a href="{{ route(($title->type == 'series' ? $title->type : $title->type.'s').'.index').'?genre='.trim($genre) }}">{{ $genre }}</a></li>
                            @endforeach
                        </ul>
                        <p class="plot">{{$episode->plot}}</p>
                        <div class="row">
                            <div class="details col-sm-8">
                                @if ( ! $title->director->isEmpty())
                                    <div class="details-block">
                                        <strong>{{ trans('main.directors') }}:</strong>
                                        <ul class="list-unstyled list-inline">
                                            @foreach($title->director as $director)
                                                <li>{{ $director->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ( ! $title->writer->isEmpty())
                                    <div class="details-block">
                                        <strong>{{ trans('main.writing') }}:</strong>
                                        <ul class="list-unstyled list-inline">
                                            @foreach($title->writer->slice(0, 3) as $writer)
                                                <li>{{ $writer->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ( ! $title->actor->isEmpty())
                                    <div class="details-block">
                                        <strong>{{ trans('main.stars') }}:</strong>
                                        <ul class="list-unstyled list-inline">
                                            @foreach($title->actor->slice(0, 3) as $actor)
                                                <li><a href="{{ Helpers::url($actor->name, $actor->id, 'people') }}">{{ $actor->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ( ! $title->season->isEmpty())
                                    <div class="details-block seasons">
                                        <strong>{{ trans('main.seasons') }}: </strong>
                                        @foreach($title->season as $item)
                                            <a href="{{ Helpers::season($title->title, $item) }}">{{ $item->number }}</a>
                                        @endforeach
                                    </div>
                                @endif

                                <div id="ratings">
                                    @if ($title->mc_user_score)
                                        <div class="rating">
                                            <img src="{{ asset('assets/images/metacritic.png') }}" class="rating-icon" alt="metacritic icon" title="metacritic.com"/>
                                            <span class="hidden-md" data-bind="raty: {{ $title->mc_user_score }}, stars: 10, readOnly: true"></span>
                                            <span class="raty-text">{{ $title->mc_user_score }}/10</span>
                                        </div>
                                    @endif

                                    @if ($title->tmdb_rating)
                                        <div class="rating">
                                            <img src="{{ asset('assets/images/tmdb.png') }}" class="rating-icon" alt="themoviedb icon" title="themoviedb.org"/>
                                            <span class="hidden-md" data-bind="raty: {{ $title->tmdb_rating }}, stars: 10, readOnly: true"></span>
                                            <span class="raty-text">{{ $title->tmdb_rating }}/10</span>
                                        </div>
                                    @endif

                                    @if ($title->imdb_rating)
                                        <strong class="raty">IMDb: </strong>
                                        <span class="hidden-md" style="padding-left: 32px" data-bind="raty: {{ $title->imdb_rating }}, stars: 5, readOnly: true"></span>
                                        <span class="raty-text">{{ $title->imdb_rating }}/10</span>
                                    @endif
                                </div>

                                @if (Hooks::hasReplace('Episodes.Show.Jumbotron') && ! Helpers::hasLinks(isset($links) ? $links : []))
                                    <button class="btn btn-primary" style="margin-top: 15px" data-toggle="modal" data-target="#add-link-modal"><i class="fa fa-plus"></i> {{ trans('stream::main.addLink') }}</button>
                                @endif
                            </div>
                            <div class="col-sm-4 secondary-details">
                                <ul class="list-unstyled">
                                    @if ($title->release_date)
                                        <li><strong>{{ trans('main.release date') .': ' }}</strong><span>{{ $title->release_date }}</span></li>
                                    @endif

                                    @if ($title->views)
                                        <li><strong>{{ trans('dash.views') .': ' }}</strong><span>{{ $title->views }}</span></li>
                                    @endif

                                    @if ($title->country)
                                        <li><strong>{{ trans('main.country') .': ' }}</strong><span>{{ $title->country }}</span></li>
                                    @endif

                                    @if ($title->language)
                                        <li><strong>{{ trans('dash.language') .': ' }}</strong><span>{{ $title->language }}</span></li>
                                    @endif

                                    @if ($title->runtime)
                                        <li><strong>{{ trans('main.runtime') .': ' }}</strong><span>{{ $title->runtime }}</span></li>
                                    @endif

                                    @if ($title->budget)
                                        <li><strong>{{ trans('main.budget') .': ' }}</strong><span>{{ $title->budget }}</span></li>
                                    @endif

                                    @if ($title->revenue)
                                        <li><strong>{{ trans('main.revenue') .': ' }}</strong><span>{{ $title->revenue }}</span></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    @if ($hasReplace = Hooks::hasReplace('Episodes.Show.Jumbotron'))
                        {{ Hooks::renderReplace('Titles.Show.LinksPanel', $links, 'links') }}
                    @endif
                </div>
            </div>
            <div class="col-sm-3 images">
                @if($title->image->count())
                    @foreach($title->image->slice(0, 2) as $img)
                        <img src="{{ $img->path }}" alt="{{ $img->title }}" class="img-responsive img-thumbnail">
                    @endforeach
                @endif
            </div>
        </div>
           <div class="row" id="episode-grid">
               <h2>{{ trans('main.otherEpsForSeason') }}</h2>
               @foreach($season->episode as $ep)
                   @if ($ep->episode_number == $episode->episode_number)
                       <div class="col-sm-6 col-md-4 col-lg-3">
                           <figure>
                               <img src="{{ Helpers::getEpisodeImage($title, $ep) }}" alt="{{ $ep->title }}" class="img-responsive">
                               <figcaption>
                                   <span>{{ trans('main.episode').' '.$ep->episode_number.' - '. $ep->title }}</span>
                               </figcaption>
                           </figure>
                       </div>
                   @else
                       <a href="{{ Helpers::episodeUrl($title->title, $title->id, $title->type, $season->number, $ep->episode_number) }}" class="col-sm-6 col-md-4 col-lg-3">
                           <figure>
                               <img src="{{ Helpers::getEpisodeImage($title, $ep) }}" alt="{{ $ep->title }}" class="img-responsive">
                               <figcaption>
                                   <span>{{ trans('main.episode').' '.$ep->episode_number.' - '. str_limit($ep->title, 25) }}</span>
                               </figcaption>
                           </figure>
                       </a>
                   @endif
               @endforeach
           </div>
           <div class="clearfix">
               <div id="disqus_thread"></div>
           </div>
       </div>
   </div>

    {{ Hooks::renderHtml('Titles.Show.BeforeScripts') }}

    <div class="modal" id="video-modal" data-src="{{$title->trailer}}">
        <div class="modal-dialog">
            <div class="modal-content">
                <button type="button" class="modal-close" data-dismiss="modal" aria-hidden="true">
                    <span class="fa-stack fa-lg">
                        <i class="fa fa-circle fa-stack-2x"></i>
                        <i class="fa fa-times fa-stack-1x fa-inverse"></i>
                    </span>
                </button>
                <div id="video-container"></div>
            </div>
        </div>
    </div>

@stop

@section('scripts')
    {{ HTML::script('themes/mercury/assets/js/scripts.js') }}

    <script>
        $('.navbar').affix({
            offset: {
                top: 300
            }
        });

        $('header').parallax({imageSrc: '<?php echo Helpers::original($title->background)?>', positionY: '0'});

        vars.title = <?php echo $title->toJson(); ?>;
        vars.disqus = '<?php echo $options->getDisqusShortname(); ?>';
        vars.titleId = '<?php echo $title->id; ?>';
        vars.trailersPlayer = '<?php echo $options->trailersPlayer(); ?>';
        vars.userId = '<?php echo Sentry::getUser() ? Sentry::getUser()->id : false ?>';
        ko.applyBindings(app.viewModels.titles.show, $('#content')[0]);
        app.viewModels.titles.create.activeSeason('<?php echo $season->number ?>');
        app.viewModels.titles.create.activeEpisode('<?php echo $episode->episode_number ?>');
        app.viewModels.titles.show.start(<?php echo Helpers::hasLinks(isset($links) ? $links : []) ? $links->first()->toJson() : null; ?>);
    </script>

    {{ Hooks::renderHtml('Titles.Show.AfterScripts') }}
@stop