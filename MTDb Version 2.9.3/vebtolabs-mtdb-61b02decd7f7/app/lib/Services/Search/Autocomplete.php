<?php namespace Lib\Services\Search;

use Helpers, Actor, Title, Response;
use Lib\Repositories\Data\TmdbData;

class Autocomplete
{
	private $order = 'tmdb_popularity';

    private $tmdb;

    /**
     * Autocomplete constructor.
     * @param TmdbData $tmdb
     */
	public function __construct(TmdbData $tmdb)
	{
		$this->tmdb = $tmdb;
	    $this->order = Helpers::getOrdering();
	}

	/**
	 * Get data to auto populate slides.
	 * 
	 * @param  string $query
	 * @return Array      
	 */
	public function sliderPopulate($query)
	{
		return Title::with('director', 'actor', 'image')
            ->whereTitleLike($this->prepareQuery($query))
            ->orderBy($this->order, 'desc')
            ->limit(8)->get()->toArray();
	}

	/**
	 * Provides autocomplete when user types
	 * in words in searchbar.
	 * 
	 * @param  string $query
	 * @return json
	 */
	public function typeAhead($query)
	{
	    if (\App::make('options')->getDataProvider() === 'tmdb') {
            return $this->tmdb->multiSearch($query);
        }

	    $query = $this->prepareQuery($query);

	    $titles = Title::limit(5)
            ->where('title', 'LIKE', $query)
            ->orderBy($this->order, 'desc')
            ->get(array('id', 'title', 'poster', 'plot', 'type'));

        $titles = $titles->map(function($title) {
            $title->link = Helpers::url($title->title, $title->id, $title->type);
            return $title;
        })->toArray();

        $actors = Actor::limit(5)
            ->where('name', 'LIKE', $query)
            ->orderBy('views', 'desc')
            ->get(array('id', 'name', 'image', 'bio'));

        $actors = $actors->map(function($actor) {
            return [
                'title'  => $actor['name'],
                'poster' => $actor['image'],
                'plot'   => $actor['bio'],
                'link'   => Helpers::url($actor->name, $actor->id, 'people'),
                'id'     => $actor->id,
            ];
        })->toArray();

	    return Response::json(array_merge($titles, $actors));
	}

	/**
	 * Provides autocomplete for actor names
	 * when attaching new actor to title.
	 * 
	 * @param  string $query
	 * @return json
	 */
	public function castTypeAhead($query)
	{	 
		$q = $this->prepareQuery($query);

	    $actors = Actor::where('name', 'LIKE', $q)
	    				->select('id', 'name', 'image', 'bio')
	    				->limit(15)
	    				->get();

	  	//add placeholder image if actor doesnt have one in db
	    foreach ($actors as $k=> $v)
	    {
	    	if ( ! $v->image)
	    	{
	    		$v->image = asset('assets/images/noimage.jpg');
	    	}
	    	elseif ( ! str_contains('http', $v->image))
	    	{
	    		$v->image = asset($v->image);
	    	}
	    }

	    return Response::json($actors);
	}

    /**
     * Prepares users search term to be run
     * against database records.
     *
     * @param  string $query
     * @return string
     */
    private function prepareQuery($query)
    {
        $query = preg_replace("/[ -.:\/&]/i", '%', $query);

        return "%$query%";
    }
}