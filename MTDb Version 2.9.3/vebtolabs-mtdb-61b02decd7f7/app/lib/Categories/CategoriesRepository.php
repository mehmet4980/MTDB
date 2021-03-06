<?php namespace Lib\Categories;

use DB, App, Cache;
use Lib\Repository;

class CategoriesRepository extends Repository
{
	/**
	 * Category model instance.
	 * 
	 * @var Category
	 */
	protected $model;

    /**
     * Data provider instance.
     *
     * @var Lib\Repositories\Data\DataProviderInterface
     */
    protected $provider;

    protected $dbWriter;

    protected $queries = array(
        'popularTitles' => array('class' => 'Lib\Titles\TitleRepository', 'method' => 'mostPopular', 'type' => 'title', 'external' => 'getPopular'),
        'latestTitles'  => array('class' => 'Lib\Titles\TitleRepository', 'method' => 'newAndUpcoming', 'type' => 'title', 'external' => 'getNowPlaying'),
        'popularActors' => array('class' => 'Lib\Actors\ActorRepository', 'method' => 'popular', 'type' => 'actor', 'external' => 'getPopularPeople'),
        'topRatedTitles'=> array('class' => 'Lib\Titles\TitleRepository', 'method' => 'topRated', 'type' => 'title', 'external' => 'getTopRated'),
    );

	public function __construct(\Category $category)
	{
        $this->model = $category;
        $this->provider = App::make('Lib\Repositories\Data\DataRepositoryInterface');
        $this->dbWriter = App::make('Lib\Services\Db\Writer');
	}

    /**
     * Return all categories.
     * 
     * @return Collection
     */
    public function all()
    {
        $model = $this->model->with(array('Title', 'Actor'))->orderBy('weight', 'desc')->get();

        //sort by pivot created_at here, because laravels sort is bugged
        //when using from closure when lazy loading relationships
        foreach ($model as $category) {
            $rel = $category->title->isEmpty() ? 'actor' : 'title';

            $category->{$rel}->sortByDesc(function($model) {
                return $model->pivot->created_at;
            });
        }

        
        return $model;
    }

    /**
     * Restrict paginate query by given params.
     *
     * @param  array $params
     * @param  Builder $query
     * @return Builder
     */
    protected function appendParams(array $params, $query)
    {
        //append all the params to query from base paginate method
        return parent::appendParams($params, $query)->with(array('Title', 'Actor'));
    }

    /**
     * Create new category or update existing one.
     * 
     * @param  array  $input
     * @return void
     */
    public function createOrUpdate(array $input)
    {
        $snakeInput = array();

        //convert all input keys to snake case
        foreach ($input as $k => $v)
        {
            $snakeInput[snake_case($k)] = $v;
        }
        
        //find a model if we get passed an id
        if (isset($snakeInput['id']) && $snakeInput['id'])
        {
            $this->model = $this->model->find($snakeInput['id']);
        }

        //make sure weight is an integer, otherwise db might error out
        if (isset($snakeInput['weight'])) {
            $snakeInput['weight'] = (integer) $snakeInput['weight'];
        }

        $this->model->fill($snakeInput)->save();

        if ($this->model->auto_update)
        {
            $this->attachByQuery($this->model->id, $this->model->query, $this->model->limit);
        }

        Cache::forget('home.content');

        return true;
    }

    /**
     * Attach title or actor to category.
     * 
     * @param  array  $input
     * @return boolean
     */
    public function attach(array $input)
    {
        if (isset($input['titleId']) && isset($input['categoryId']))
        {   
            Cache::forget('home.content');
            
            try {
                $this->model->find($input['categoryId'])->{$input['type']}()->attach($input['titleId']);
                return true;
            } catch (Exception $e) {}
        }
    }

    /**
     * Detach title or actor from category.
     * 
     * @param  array  $input
     * @return boolean
     */
    public function detach(array $input)
    {
        if (isset($input['titleId']) && isset($input['categoryId']))
        {
            try {
                Cache::forget('home.content');     
                return $this->model->find($input['categoryId'])->{$input['type']}()->detach($input['titleId']);
            } catch (Exception $e) {}
        }
    }

    /**
     * Update the given category with new titles.
     *
     * @param  Category $category
     * @return Category
     */
    public function updateCategory($category)
    {
        $method = $this->queries[$category->query]['external'];
        
        //update using external data provider
        if ($category->update_from_external && $this->provider->name !== 'db' && $method !== 'getPopularPeople') {
            $titles = $this->provider->{$method}($category->limit);
            $name = ucfirst($this->provider->name);
            $titles = $this->dbWriter->{'insertFrom'.$name.'Search'}($titles);
        }

        //update from local database
        return $this->attachByQuery($category->id, $category->query, $category->limit, isset($titles) ? $titles : null);
    }

    /**
     * Fetch titles via given query and attach
     * them to the given category.
     * 
     * @param  mixed   $id
     * @param  string  $query
     * @param  integer $limit
     * 
     * @return Category
     */
    public function attachByQuery($id, $query, $limit = 8, $titles = null)
    {
        if (isset($this->queries[$query]))
        {
            $this->detachAll($id);

            //get class name and method name
            extract($this->queries[$query]);

            //fetch titles via query method on the class
            if ( ! isset($titles)) {
                $titles = App::make($class)->$method($limit);
            }
            
            $sync = array();

            foreach ($titles->lists('id') as $key => $tid)
            {
               $sync[] = $tid;
            }

            //attach fetched categorizable resources to the category
            $this->model->find($id)->$type()->sync($sync);

            Cache::forget('home.content');

            return $this->model->with(ucfirst($type))->find($id);
        }
    }

    /**
     * Detach all titles and actors from given category.
     * 
     * @param  mixed $id
     * @return void
     */
    public function detachAll($id)
    {
        $this->model->find($id)->title()->detach();
        $this->model->actor()->detach();
        Cache::forget('home.content');
    }

    /**
     * delete a category with given id.
     * 
     * @param  integer $id
     * @return boolean
     */
    public function delete($id)
    {
        if ($id)
        {   
            $this->model->destroy($id);
            DB::table('categorizables')->where('category_id', $id)->delete();
            Cache::forget('home.content');
            return true;
        }
    }
}