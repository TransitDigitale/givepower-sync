<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Spectra extends Model
{
    //
    protected $table = 'spectras';
    protected $primary = 'spectra_id';



    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'spectra_id', 'created_at', 'updated_at',
    ];


    public static function insertIgnore(array $attributes = [])
    {
        $model = new static($attributes);

        if ($model->usesTimestamps()) {
            $model->updateTimestamps();
        }

        $attributes = $model->getAttributes();

        $query = $model->newBaseQueryBuilder();
        $processor = $query->getProcessor();
        $grammar = $query->getGrammar();

        $table = $grammar->wrapTable($model->getTable());
        $keyName = $model->getKeyName();
        $columns = $grammar->columnize(array_keys($attributes));
        $values = $grammar->parameterize($attributes);

        $sql = "insert ignore into {$table} ({$columns}) values ({$values})";

        $id = $processor->processInsertGetId($query, $sql, array_values($attributes));

        $model->setAttribute($keyName, $id);

        return $model;
    }


    /**
	* @see https://stackoverflow.com/a/25472319/470749
	* 
	* @param array $arrayOfArrays
	* @return bool
	*/
	public static function insertIgnoreMulti($arrayOfArrays) {
	$static = new static();
	$table = with(new static)->getTable(); //https://github.com/laravel/framework/issues/1436#issuecomment-28985630
	$questionMarks = '';
	$values = [];
	foreach ($arrayOfArrays as $k => $array) {
	    if ($static->timestamps) {
	        $now = \Carbon\Carbon::now();
	        $arrayOfArrays[$k]['created_at'] = $now;
	        $arrayOfArrays[$k]['updated_at'] = $now;
	        if ($k > 0) {
	            $questionMarks .= ',';
	        }
	        $questionMarks .= '(?' . str_repeat(',?', count($array) - 1) . ')';
	        $values = array_merge($values, array_values($array));//TODO
	    }
	}
	$query = 'INSERT IGNORE INTO ' . $table . ' (' . implode(',', array_keys($array)) . ') VALUES ' . $questionMarks;
	return \DB::insert($query, $values);
	}


}
