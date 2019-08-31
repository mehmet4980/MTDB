@extends('Main.Boilerplate')

@section('bodytag')
    <body id="titles-index">
@stop

@section('assets')
    @parent

    <meta name="fragment" content="!"/>
    <meta name="title" content="{{ trans('main.meta title') }}"/>
    <meta name="description" content="{{ trans('main.meta description') }}"/>
    <meta name="keywords" content="{{ trans('main.meta keywords') }}"/>

    {{ HTML::style('themes/mercury/assets/css/pikaday.css') }}
@stop

@section('content')

    <div class="page-wrapper">
        <header class="header">
            <div class="overlay"></div>
        </header>

        <div class="container" id="content">

            <div class="clearfix">
                <div class="index-pagination"></div>
            </div>

            <div class="row">
                <section data-bind="foreach: {data: sourceItems, afterRender: lazyLoadImage}" class="col-sm-8">
                    <div class="media">
                        <div class="pull-left col-sm-3">
                            {{ Hooks::renderHtml('Titles.Index.ForEachMovie') }}
                            <a data-bind="attr: { href: vars.urls.baseUrl+'/'+vars.trans[type]+'/'+id+'-'+title.replace(/\s+/g, '-').replace('/', '-').toLowerCase() }">
                                <img class="img-responsive" data-bind="attr: { 'data-original': poster, alt: title }">
                            </a>
                        </div>
                        <div class="media-body">
                            <h3 class="media-heading"><a data-bind="text: title+' ('+year+')', attr: { href: vars.urls.baseUrl+'/'+vars.trans[type]+'/'+id+'-'+title.replace(/\s+/g, '-').replace('/', '-').toLowerCase() }"></a></h3>
                            <ul class="list-unstyled list-inline genres" data-bind="foreach: {data: genre.split('|')}">
                                <li><a data-bind="text: $data, attr: {href: $parent.type == 'movie' ? vars.urls.movies+'?genre='+$data.trim() : vars.urls.series+'?genre='+$data.trim()}"></a></li>
                            </ul>
                            <p data-bind="text: plot"></p>
                            <div class="ratings">
                                <!-- ko if: mc_user_score -->
                                <div class="rating">
                                    <img src="assets/images/metacritic.png" class="rating-icon" alt="metacritic icon" title="metacritic.com"/>
                                    <span class="hidden-md" data-bind="raty: mc_user_score, stars: 10, readOnly: true"></span>
                                    <span class="raty-text" data-bind="text: mc_user_score + '/10'"></span>
                                </div>
                                <!-- /ko -->

                                <!-- ko if: tmdb_rating > 0 -->
                                <div class="rating">
                                    <img src="assets/images/tmdb.png" class="rating-icon" alt="themoviedb icon" title="themoviedb.org"/>
                                    <span class="hidden-md" data-bind="raty: tmdb_rating, stars: 10, readOnly: true"></span>
                                    <span class="raty-text" data-bind="text: tmdb_rating + '/10'"></span>
                                </div>
                                <!-- /ko -->

                                <!-- ko if: imdb_rating -->
                                <img src="assets/images/imdb.png" class="rating-icon" alt="themoviedb icon" title="themoviedb.org"/>
                                <span class="hidden-md" data-bind="raty: imdb_rating, stars: 5, readOnly: true"></span>
                                <span class="raty-text" data-bind="text: imdb_rating + '/10'"></span>
                                <!-- /ko -->
                            </div>
                            <div class="details">
                                <strong>{{trans('main.release date')}}: </strong>
                                <span data-bind="text: release_date"></span>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="filter-panel col-sm-3 col-sm-offset-1">
                    {{ Hooks::renderHtml('Titles.Index.UnderFilters') }}
                    <div class="filter-label">{{trans('dash.genres')}}</div>
                    <div class="genre-box">
                        @foreach ($options->getGenres() as $genre)
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" class="checkbox" value="{{strtolower($genre)}}" data-bind="checked: params.genres"/> {{ $genre }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group border-top">
                        <label for="search">{{ trans('dash.searchByTitle') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="Jurassic World..." data-bind="value: params.query, valueUpdate: 'keyup'">
                    </div>

                    <div class="form-group">
                        <label for="fort">{{ trans('dash.orderBy') }}</label>
                        <select name="sort" class="form-control" data-bind="value: params.order">
                            <option value="release_dateDesc">{{ trans('dash.relDateDesc') }}</option>
                            <option value="release_dateAsc">{{ trans('dash.relDateAsc') }}</option>
                            <option value="mc_user_scoreDesc">{{ trans('dash.rateDesc') }}</option>
                            <option value="mc_user_scoreAsc">{{ trans('dash.rateAsc') }}</option>
                            <option value="mc_num_of_votesDesc">{{ trans('dash.rateNumDesc') }}</option>
                            <option value="mc_num_of_votesAsc">{{ trans('dash.rateNumAsc') }}</option>
                            <option value="titleAsc">{{ trans('dash.titleAsc') }}</option>
                            <option value="titleDesc">{{ trans('dash.titleDesc') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cast">{{ trans('dash.haveActor') }}</label>
                        <input type="text" name="cast" class="form-control" placeholder="Chris Pratt..." data-bind="value: params.cast, valueUpdate: 'keyup'">
                    </div>

                    <div class="form-group">
                        <label for="date-before">{{ trans('dash.relBefore') }}</label>
                        <input class="form-control date-picker" placeholder="2013-01-05" data-bind="value: params.before, picker: 'before'">
                    </div>

                    <div class="form-group">
                        <label for="date-after">{{ trans('dash.relAfter') }}</label>
                        <input class="form-control date-picker" placeholder="2015-09-15"  data-bind="value: params.after, picker: 'after'">
                    </div>

                    <div class="form-group">
                        <label for="minRating">{{ trans('dash.minRating') }}</label>
                        <select name="minRating" class="form-control" data-bind="value: params.minRating">
                            <option value="">{{trans('dash.any')}}</option>
                            @foreach(range(1, 10) as $num)
                                <option value="{{ $num }}">{{ $num }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="maxRating">{{ trans('dash.maxRating') }}</label>
                        <select name="maxRating" class="form-control" data-bind="value: params.maxRating">
                            <option value="">{{trans('dash.any')}}</option>
                            @foreach(range(1, 10) as $num)
                                <option value="{{ $num }}">{{ $num }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="clearfix">
                <div class="index-pagination bottom-pagination"></div>
            </div>

        </div>
    </div>

@stop

@section('scripts')
    <script>
        $('.navbar').affix({
            offset: {
                top: 180
            }
        });

        $('.filter-panel > .checkbox').iCheck({
            checkboxClass: 'icheckbox_square-aero',
            radioClass: 'iradio_square-aero'
        }).on('ifChecked', function(e) {
            app.viewModels.titles.index.params.availToStream(true);
        }).on('ifUnchecked', function(e) {
            app.viewModels.titles.index.params.availToStream(false);
        });

        $('.genre-box .checkbox').iCheck({
            checkboxClass: 'icheckbox_square-aero',
            radioClass: 'iradio_square-aero'
        }).on('ifChecked', function(e) {
            app.viewModels.titles.index.params.genres.push(e.delegateTarget.value);
        }).on('ifUnchecked', function(e) {
            app.viewModels.titles.index.params.genres.remove(e.delegateTarget.value);
        });

        app.viewModels.titles.index.params.availToStream && app.viewModels.titles.index.params.availToStream(<?php echo $options->checkAvailToStream(); ?>);
        app.viewModels.titles.index.start('<?php echo $type; ?>');

        app.paginator.addCallback(function(data) {
            if (data.items && data.items.length) {
                var url    = data.items[0].background.replace('w780', 'original'),
                    mirror = $('.parallax-slider'),
                    background = $('.header');

                if (mirror[0]) {
                    mirror.attr('src', url);
                } else {
                    background.parallax({imageSrc: url, positionY: '0', zIndex: 2});
                }
            }
        });
    </script>
@stop
