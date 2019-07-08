<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;


class DeviceController extends Controller
{

    protected $amount = 1000;
    protected $key = 0;
    protected $pairs = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // URL Examples

        // Select fields
        // /api/v1/user?fields=id,nom_user,sexe,created_at

        // Pagination, LIMIT 20 OFFSET 10 or Limit 10, 20
        // /api/v1/user?key=1&amount=5

        // Sorting (field, direction)
        // /api/v1/user?sort=full_name,desc

        // Where (AND Where, OR Where) clause
        // (where=field1,operand1,value1;AND:field2,operand2,value2,OR:field3,operand3,value3)
        // /api/v1/user?where=full_name[,]=[,]Jimmy%20Bruce[;]OR:id[,]%3E=[,]2

        // /api/v1/user?fields=id,full_name,sexe,created_at&sort=full_name,desc&key=1&amount=5&where=full_name[,]=[,]Jimmy%20Bruce[;]OR:id[,]%3E=[,]2

        // Keep track of response time
        $start = microtime(true);

        
        $Entity = static::$resourceModels[$this->resource];

        $EntityClass = 'App\\'.$Entity;

        $request = request();


        // Fields (SELECT ... ASC, DESC in DB) ----
        $models = $EntityClass::select("*");
        $this->applyFieldsParam($request, $models, $EntityClass);

        // Relations to load (Eloquent relationship) ----
        $this->applyRelationsParam($request, $models, $EntityClass);

        // Where (WHERE ... AND ... OR) ----
        $this->applyWhereParam($request, $models);

        // Sort (ORDER BY ... ASC, DESC in DB) ----
        $this->applySortParam($request, $models);

        // Key (TOP ... in DB) ----
        $this->applyKeyParam($request, $models);

        // Amount (LIMIT ... in DB) ----
        $this->applyAmountParam($request, $models);


        // Process the Query in Database
        $models = $models->get();

        // Put Key => Value PAIRS

        $pairs['amount'] = $this->amount;
        $pairs['key'] = $this->key;
        $pairs['total'] = $EntityClass::count();
        $pairs['records'] = $models->count();

        // Note: Do not use array_merge(). array_merge() overwrites keys and does not preserve numeric keys.
        // https://stackoverflow.com/a/29218054/5565544
        $this->pairs = $pairs + $this->pairs;

        $end = microtime(true);
        $elapsed = $end - $start;

        return $this->responseSuccess($models, 200, $elapsed, $this->pairs);
    }



    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
        $Entity = static::$resourceModels[$this->resource];

        $EntityClass = 'App\\'.$Entity;

        
        // Fields (SELECT ... ASC, DESC in DB) ----
        $models = $EntityClass::select("*");
        $this->applyFieldsParam($request, $models, $EntityClass);

        // Relations to load (Eloquent relationship) ----
        $this->applyRelationsParam($request, $models, $EntityClass);


        // Process the Query in Database
        $model = $models->find($id);

        if(!$model)
            return $this->responseResourceNotFoundError();

        // Check Policy
        /*if(\Auth::check()) {
            $user = \Auth::user();
            if(!$user->can('view', $model)) {
                return $this->responseError('Authenticated User is not allowed to view this '.$Entity, 403);
            }
        }*/


        return $this->responseSuccess($model, 200);
    }


    public function responseSuccess($data, $code = 200, $time = null, $pairs = []) {
        $resp = [];
        $resp['success'] = true;

        // Pass key => value PAIRS
        foreach($pairs as $key => $value) {
            $resp[$key] = $value;
        }

        if($data instanceof Model) {
            $className = snake_case((new \ReflectionClass($data))->getShortName());
            $resp[$className] = $data;
        } else {
            $resp['results'] = $data;
        }

        return response()->json($resp, $code);
    }

    public function responseError($message, $code) {
        return response()->json([
            "success" => false,
            "message" => $message
        ], $code);
    }

    public function responseResourceNotFoundError() {
        return $this->responseError('resource not found', 401);
    }

    protected function applyKeyParam(Request $request, $models) {
        // Key (TOP ... in DB) ----
        if($request->filled('key')) {
            $this->key = (int) $request->input('key');
        }
        $models = $models->skip($this->key);
    }

    protected function applyAmountParam(Request $request, $models) {
        // Amount (LIMIT ... in DB) ----
        if($request->filled('amount')) {
            $this->amount = (int) $request->input('amount');
        }
        $models = $models->take($this->amount);
    }

    protected function applyFieldsParam(Request $request, $models, $EntityClass) {
         // Fields (SELECT ... ASC, DESC in DB) ----
        if($request->filled('fields')) {
            $fields = explode(',', $request->input('fields'));

            $models = $models->select($fields);
            //$models = call_user_func_array($EntityClass."::select", $fields);
        }
    }

    protected function applyRelationsParam(Request $request, $models, $EntityClass) {
        // Relations to load (Eloquent relationship) ----
        if($request->filled('relations')) {
            if($request->relations == 'all') {
                // Load all the new Laravel Database Relations
                // Avoid Old Backendless Relations
                $relations = $EntityClass::$arrayRelations;
                $relations = array_filter($relations, function($string) {
                    return strpos($string, 'backend') === false;
                });

            } else {
                $relations = explode(',', $request->input('relations'));
            }

            $models = $models->with($relations);
        }
    }

    protected function applyWhereParam(Request $request, $models) {
        if($request->filled('where')) {

            // Multidimensional where, use array in URL params
            //$whereLevels = $request->get('where');
            // parse to array
            //$whereLevels = eval("return " . $whereLevels . ";");
            //dd($whereLevels);

            // WHERE Pattern
            // /api/user?where=nom_user[,]=[,]Jimmy Bruce[;]OR:id[,]%3E=[,]2

            $whereLevels = explode('[;]', $request->input('where'));
            foreach($whereLevels as $whereLevel) {

                $wheres = explode('[,]', $whereLevel);

                // check where operand. Ex: => OR:nom_user, AND:created_at
                $whereOperands = explode(':', $wheres[0]);

                // /api/user?where=nom_user[,]=[,]Jimmy Bruce[;]OR:id[,]%3E=[,]2
                if(count($whereOperands) > 1) {
                    // Check if Operand type is "OR" to call orWhere() method
                    if($whereOperands[0] == 'OR') {

                        // $whereOperands[0] is field name. Ex: nom_user

                        if(count($wheres) == 3) {
                            // /api/user?where=nom_user[,]=[,]
                            if($wheres[1] == 'like') {
                                // If using like, then user should pass ** instead of %,
                                // because % in URL causes problem with numbers
                                // + character in URL causes problem of URL encoding
                                $whereValue = str_replace('**', '%', $wheres[2]);
                                $models = $models->orWhere($whereOperands[1], $wheres[1], $whereValue);
                            }
                            else {
                                $models = $models->orWhere($whereOperands[1], $wheres[1], $wheres[2]);
                            }

                        } else {
                            // /api/vente?where=member_id[,]notNull
                            // /api/vente?where=member_id[,]null
                            if($wheres[1] == 'null') {
                                $models = $models->orWhereNull($whereOperands[1]);
                            } else if($wheres[1] == 'notNull') {
                                $models = $models->orWhereNotNull($whereOperands[1]);
                            }
                        }

                    } else if($whereOperands[0] == 'AND'){

                        if(count($wheres) == 3) {
                            // /api/user?where=nom_user[,]=[,]
                            if($wheres[1] == 'like') {
                                // If using like, then user should pass ** instead of %,
                                // because % in URL causes problem with numbers
                                // + character in URL causes problem of URL encoding
                                $whereValue = str_replace('**', '%', $wheres[2]);
                                $models = $models->where($whereOperands[1], $wheres[1], $whereValue);
                            }
                            else {
                                $models = $models->where($whereOperands[1], $wheres[1], $wheres[2]);
                            }
                        } else {
                            // /api/vente?where=member_id[,]notNull
                            // /api/vente?where=member_id[,]null
                            if($wheres[1] == 'null') {
                                $models = $models->whereNull($whereOperands[1]);
                            } else if($wheres[1] == 'notNull') {
                                $models = $models->whereNotNull($whereOperands[1]);
                            }
                        }
                    }
                } else {

                    if(count($wheres) == 3) {
                        // /api/user?where=nom_user[,]=[,]
                        if($wheres[1] == 'like') {
                                // If using like, then user should pass ** instead of %,
                                // because % in URL causes problem with numbers
                                // + character in URL causes problem of URL encoding
                                $whereValue = str_replace('**', '%', $wheres[2]);
                                $models = $models->where($wheres[0], $wheres[1], $whereValue);
                            }
                            else {
                                $models = $models->where($wheres[0], $wheres[1], $wheres[2]);
                            }

                    } else {
                        // /api/vente?where=member_id[,]notNull
                        // /api/vente?where=member_id[,]null
                        if($wheres[1] == 'null') {
                            $models = $models->whereNull($wheres[0]);
                        } else if($wheres[1] == 'notNull')  {
                            $models = $models->whereNotNull($wheres[0]);
                        }
                    }

                    
                }
            }
        }
    }

    protected function applySortParam(Request $request, $models) {
        // Sort (ORDER BY ... ASC, DESC in DB) ----
        if($request->filled('sort')) {
            $sorts = explode(',', $request->input('sort'));
            $models = $models->orderBy($sorts[0], $sorts[1]);
        }
    }

    protected function applyBetweenParam(Request $request, $models, $field = 'created_at') {
        if($request->filled('between')) {

            // BETWEEN Pattern
            // /api/user?between=created_at[,]2018-01-01[,]2018-06-01

            //$betweens = explode(',', $request->input('between'));
        }
    }

}
