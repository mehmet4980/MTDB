<div class="row details-panel">
    <div class="col-sm-3 poster hidden-sm">
        <img src="{{$title->poster}}" alt="{{$title->title}}" class="img-responsive"/>
        @if($title->trailer)
            <button class="btn btn-block btn-primary" data-bind="click: showTrailerModal"><i class="fa fa-play"></i> Watch Trailer</button>
        @endif
    </div>
    <div class="col-md-9 col-sm-12">
        <h1><a href="{{Helpers::url($title->title, $title->id, $title->type)}}">{{$title->title}} ({{$title->year}})</a></h1>
        <ul class="list-unstyled list-inline genres">
            @foreach(explode('|', $title->genre) as $genre)
                <li><a href="{{ route(($title->type == 'series' ? $title->type : $title->type.'s').'.index').'?genre='.trim($genre) }}">{{ $genre }}</a></li>
            @endforeach
        </ul>
        <p class="plot">{{$title->plot}}</p>
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
                        @foreach($title->season as $season)
                            <a href="{{ Helpers::season($title->title, $season) }}">{{ $season->number }}</a>
                        @endforeach
                    </div>
                @endif

                <div id="ratings">
                    @if ($title->mc_user_score)
                        <div class="rating">
                            <img src="{{ asset('assets/images/metacritic.png') }}" class="rating-icon" alt="metacritic icon" title="metacritic.com"/>
                            <span data-bind="raty: {{ $title->mc_user_score }}, stars: 10, readOnly: true"></span>
                            <span class="raty-text">{{ $title->mc_user_score }}/10</span>
                        </div>
                    @endif

                    @if ($title->tmdb_rating)
                        <div class="rating">
                            <img src="{{ asset('assets/images/tmdb.png') }}" class="rating-icon" alt="themoviedb icon" title="themoviedb.org"/>
                            <span data-bind="raty: {{ $title->tmdb_rating }}, stars: 10, readOnly: true"></span>
                            <span class="raty-text">{{ $title->tmdb_rating }}/10</span>
                        </div>
                    @endif

                    @if ($title->imdb_rating)
                        <div class="rating">
                            <img src="{{ asset('assets/images/imdb.png') }}" class="rating-icon" alt="IMDb icon" title="imdb.org"/>
                            <span data-bind="raty: {{ $title->imdb_rating }}, stars: 10, readOnly: true"></span>
                            <span class="raty-text">{{ $title->imdb_rating }}/10</span>
                        </div>
                    @endif
                </div>
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
</div>