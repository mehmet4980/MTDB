<header class="nav-header">
	<nav class="navbar navbar-inverse" role="navigation">
		<div class="container">
			<a class="logo" href="{{ route('home') }}">
				<img class="brand-logo" src="{{ $options->getLogo() }}">
			</a>

			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<div class="collapse collapse-container" id="navbar-collapse">
				{{-- main navigation --}}
				<ul class="navigation-items">
					{{ HTML::getMenu('header') }}
				</ul>
				{{-- /main navigation --}}

				<ul class="logged-in-box">
					@if( ! Sentry::check())
						<li><a href="{{ url(Str::slug(trans('main.register'))) }}">{{ trans('main.register-menu') }}</a></li>
						<li><a href="{{ url(Str::slug(trans('main.login'))) }}">{{ trans('main.login-menu') }}</a></li>
					@else
						<li class="dropdown simple-dropdown hidden-xs" id="logged-in-box">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<img src="{{ Helpers::smallAvatar() }}" class="small-avatar">
								<span class="display_name">{{{ Helpers::loggedInUser()->first_name ? Helpers::loggedInUser()->first_name : Helpers::loggedInUser()->username }}}</span> <b class="caret"></b>
							</a>
							<ul class="dropdown-menu" role="menu">
								@if(Helpers::hasAccess('super'))
									<li><a href="{{ url('dashboard') }}">{{ trans('dash.dashboard') }}</a></li>
								@endif
								<li><a href="{{ route('users.show', Helpers::loggedInUser()->id) }}">{{ trans('users.profile') }}</a></li>
								<li><a href="{{ route('users.edit', Helpers::loggedInUser()->id) }}">{{ trans('dash.settings') }}</a></li>
								<li><a href="{{ action('SessionController@logOut') }}"> {{ trans('main.logout') }}</a></li>

							</ul>
						</li>

						<li class="visible-xs"><a href="{{ route('users.show', Helpers::loggedInUser()->id) }}">{{ trans('users.profile') }}</a></li>
						<li class="visible-xs"><a href="{{ route('users.edit', Helpers::loggedInUser()->id) }}">{{ trans('dash.settings') }}</a></li>
						<li class="visible-xs"><a href="{{ action('SessionController@logOut') }}"> {{ trans('main.logout') }}</a></li>

					@endif
				</ul>
			</div>
		</div>
	</nav>

	<form class="search-bar" method="GET" action="{{route('search')}}">
		<div class="container">
			<input class="form-control search-input" placeholder="{{ trans('main.search') }}..." autocomplete="off" data-bind="value: query, valueUpdate: 'keyup', hideOnBlur" name="q" type="search">
			<button class="search-button" type="submit"><i class="fa fa-search"></i></button>

			<div class="autocomplete-container">
				<section class="results" data-bind="foreach: autocompleteResults.slice(0,5)">
					<div class="result">
						<a class="result-image" data-bind="attr: {href: link}">
							<img class="media-object img-responsive" data-bind="attr: { src: poster, alt: title }">
						</a>
						<a class="result-body" data-bind="attr: {href: link}">
							<div class="result-title" data-bind="text: title"></div>
							
							<!-- ko if: $data.release_date -->
								<div class="result-release-date" data-bind="text: release_date"></div>
							<!-- /ko -->
							

							<p data-bind="text: plot ? plot.substring(0,250)+'...' : ''"></p>

							<div class="result-type" data-bind="text: $data.type ? type : 'actor'"></div>
						</a>
					</div>
				</section>
			</div>
		</div>
	</form>
</header>