<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DataController extends Controller
{

    protected $user_id;

    public function __construct()
    {
        $mydata = new AuthController();
        $this->user_id = $mydata->me()->getData()->user_id;

        $this->middleware('auth:api', ['except' => ['getProduct']]);
    }


    public function getProduct
    (
        $type,
        $limit,
        $order,
        Request $request
    )
    {
        $user_id = $this->user_id;
        
        if($type == 'all'){

            if(!empty($limit) AND 
            is_numeric($limit) AND 
            !empty($order) AND 
            $order == 'asc' OR 
            $order == 'desc' 

        ){
            return DB::table('Wo_Custom_Store')
                ->distinct()
                ->select(
                    'Wo_Products.id', 
                    'Wo_Custom_Store.user_id',
                    'Wo_Langs.english',
                    'Wo_Products_Media.image', 
                    'Wo_Custom_Store.logo', 
                    'Wo_Custom_Store.banner', 
                    'Wo_Products.name', 
                    'Wo_Products.description', 
                    'Wo_Products.category', 
                    'Wo_Products.price', 
                    'Wo_Products.remark', 
                    'Wo_Products.expire'
                )
                ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
                ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
                ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

                ->leftJoin('Wo_Langs', function($q) {
                    $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
                    $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
                })

                ->leftJoin('Wo_Products_Media', 'Wo_Products.id', '=', 'Wo_Products_Media.product_id')
                ->where(['Wo_Custom_Store.user_id' => $user_id, 'Wo_Custom_Store.status' => 1])
                ->groupBy('Wo_Products.id')
                ->orderBy('id', $order)
                ->paginate($limit);
            }else{
                return response()->json(['error' => 'Some Of Value Are Invalid..'], 404);
            }
        }elseif($type == 'products' OR $type == 'offer' OR $type == 'service' OR $type == 'shouts' OR $type == 'article'){
            $products_type = ['products', 'offer', 'service', 'shouts'];

            if(in_array($type, $products_type) AND $type !== 'article'){
                

                if(
                    !empty($limit) AND 
                    is_numeric($limit) AND 
                    !empty($order) AND 
                    $order == 'asc' OR 
                    $order == 'desc' 
                ){
                    return DB::table('Wo_Custom_Store')
                    ->distinct()
                    ->select(
                        'Wo_Products.id', 
                        'Wo_Custom_Store.user_id',
                        'Wo_Langs.english',
                        'Wo_Products_Media.image', 
                        'Wo_Custom_Store.logo', 
                        'Wo_Custom_Store.banner', 
                        'Wo_Products.name', 
                        'Wo_Products.description', 
                        'Wo_Products.category', 
                        'Wo_Products.price', 
                        'Wo_Products.remark', 
                        'Wo_Products.expire'
                    )
                    ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
                    ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
                    ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

                    ->leftJoin('Wo_Langs', function($q) {
                        $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
                        $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
                    })

                    ->leftJoin('Wo_Products_Media', 'Wo_Products.id', '=', 'Wo_Products_Media.product_id')
                    ->where(['Wo_Products.remark' => $type == 'products' ? '' : $type , 'Wo_Custom_Store.user_id' => $user_id, 'Wo_Custom_Store.status' => 1])
                    ->groupBy('Wo_Products.id')
                    ->orderBy('id', $order)
                    ->paginate($limit);
                }else{
                    return response()->json(['error' => 'Some Of Value Are Invalid..'], 404);
                }
            }elseif($type == 'article'){

                if(
                    !empty($limit) AND 
                    is_numeric($limit) AND 
                    !empty($order) AND 
                    $order == 'asc' OR 
                    $order == 'desc' 
                ){
                    return DB::table('Wo_Blog')
                    ->select(
                        'Wo_Blog.id', 
                        'Wo_Blog.title', 
                        'Wo_Blog.content', 
                        'Wo_Blog.thumbnail', 
                        'Wo_Blog.tags', 
                        'Wo_Langs.english',
                        'Wo_Cs_BlogCount.count as visited',
                    )
                    ->leftJoin('Wo_Blogs_Categories', 'Wo_Blog.category', '=', 'Wo_Blogs_Categories.id')
                    ->leftJoin('Wo_Langs', 'Wo_Blogs_Categories.lang_key', '=', 'Wo_Langs.id')
                    ->leftJoin('Wo_Cs_BlogCount', 'Wo_Cs_BlogCount.blog', '=', 'Wo_Blog.id')
                    ->where(['Wo_Blog.user' => $user_id])
                    ->orderBy('Wo_Blog.id', $order)
                    ->paginate($limit);
                }else{
                    return response()->json(['error' => 'Some Of Value Are Invalid..'], 400);
                }

            }else{
                return response()->json(['error' => 'Query Not Found'], 400);
            }

        }else{
            return response()->json(['error' => 'Query Not Found'], 400);
        }
    }

    public function getSingleProduct
    (
        $type,
        $id,
        Request $request
    ){

        $user_id = $this->user_id;

        $products_type = ['products', 'offer', 'service', 'shouts'];

        if(in_array($type, $products_type) AND $type !== 'article'){

            if(!empty($type) AND !empty($id) AND is_numeric($id)){

                $single = DB::table('Wo_Custom_Store')
                ->distinct()
                ->select(
                    'Wo_Custom_Store.user_id',
                    'Wo_Langs.english',
                    'Wo_Products_Media.image',
                    'Wo_Custom_Store.logo',
                    'Wo_Custom_Store.banner',
                    'Wo_Products.id',
                    'Wo_Products.name',
                    'Wo_Products.location',
                    'Wo_Products.type',
                    'Wo_Products.type2',
                    'Wo_Products.description',
                    'Wo_Products.category',
                    'Wo_Products.price',
                    'Wo_Products.remark',
                    'Wo_Products.expire'
                )
                ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
                ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
                ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

                ->leftJoin('Wo_Langs', function($q) {
                    $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
                    $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
                })

                ->leftJoin('Wo_Products_Media', 'Wo_Products.id', '=', 'Wo_Products_Media.product_id')
                ->where([
                    'Wo_Products.remark' => $type == 'products' ? '' : $type, 
                    'Wo_Products.id' => $id,
                    'Wo_Custom_Store.user_id' => $user_id, 
                    'Wo_Custom_Store.status' => 1
                ])
                ->groupBy('Wo_Products.id')
                ->first();

                if(empty($single)){
                    return response()->json(['error' => 'Product Id Doesn\'t Exists'], 404);
                }


                $images = DB::table('Wo_Products_Media')->where('product_id', $id)->get();

                $categories_rand = DB::table('Wo_Products')->where('category', $single->category)->limit(4)->get();
                
                $data = [
                    'data' => $single,
                    'images' => $images,
                    'related' => $categories_rand
                ];

                return response()->json($data);

            }else{
                return response()->json(['error' => 'Some Of Value Are Invalid..'], 404);
            }
        }elseif($type == 'article'){
            if(!empty($type) AND !empty($id) AND is_numeric($id)){

                 $single = DB::table('Wo_Blog')
                ->select(
                    'Wo_Blog.title as name',
                    'Wo_Blog.content as description',
                    'Wo_Blog.thumbnail as image',
                    'Wo_Blog.tags',
                    'Wo_Blog.category',
                    'Wo_Langs.english',
                    'Wo_Custom_Store.logo'
                )
                ->leftJoin('Wo_Blogs_Categories', 'Wo_Blog.category', '=', 'Wo_Blogs_Categories.id')
                ->leftJoin('Wo_Langs', 'Wo_Blogs_Categories.lang_key', '=', 'Wo_Langs.id')
                ->leftJoin('Wo_Custom_Store', 'Wo_Blog.user', '=', 'Wo_Custom_Store.user_id')
                ->where([
                    'Wo_Blog.id' => $id,
                    'Wo_Custom_Store.user_id' => $user_id, 
                    'Wo_Custom_Store.status' => 1
                ])
                ->first();
                

                if(empty($single)){
                    return response()->json(['error' => 'Article Id Doesn\'t Exists'], 404);
                }


                $comments = DB::table('Wo_Cs_Blog_Comments')
                ->where([
                    'blog_id' => $id,
                    'user_id' => $user_id, 
                    'status' => 1
                ])->orderby('parent_id', 'asc')->get();


                $blog_count = DB::table('Wo_Cs_BlogCount')->select('count')->where('blog', $id)->first();
                


                $categories_rand = DB::table('Wo_Blog')->where('category', $single->category)->limit(4)->get();




                $data = [
                    'data' => $single,
                    'comments' => $comments,
                    'visited' => $blog_count,
                    'categories' => $categories_rand,
                ];
                

                return response()->json($data);
            }
        }
    }


    public function MyAllCategories()
    {
        $user_id = $this->user_id;

        return DB::table('Wo_Custom_Store')
        ->distinct()
        ->select(
            'Wo_Custom_Store.user_id',
            'Wo_Langs.english',
            'Wo_Products.id',
            'Wo_Products.category'
        )
        ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
        ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
        ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

        ->leftJoin('Wo_Langs', function($q) {
            $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
            $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
        })
        ->where(['Wo_Custom_Store.user_id' => $user_id, 'Wo_Custom_Store.status' => 1])
        ->groupBy('Wo_Langs.english')
        ->orderBy('Wo_Langs.english', 'asc')
        ->get();
    }

    public function categoriesSingle($single, $limit = null)
    {
        $user_id = $this->user_id;

        if(!empty($single) AND !empty($limit) AND is_numeric($limit)){
            return DB::table('Wo_Custom_Store')
            ->distinct()
            ->select(
                'Wo_Products.id', 
                'Wo_Custom_Store.user_id',
                'Wo_Langs.english',
                'Wo_Products_Media.image', 
                'Wo_Custom_Store.logo', 
                'Wo_Custom_Store.banner', 
                'Wo_Products.name', 
                'Wo_Products.description', 
                'Wo_Products.category', 
                'Wo_Products.price', 
                'Wo_Products.remark', 
                'Wo_Products.expire'
            )
            ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
            ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
            ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

            ->leftJoin('Wo_Langs', function($q) {
                $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
                $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
            })

            ->leftJoin('Wo_Products_Media', 'Wo_Products.id', '=', 'Wo_Products_Media.product_id')
            ->where(['Wo_Custom_Store.user_id' => $user_id, 'Wo_Custom_Store.status' => 1])
            ->where('Wo_Products.category', $single)
            ->groupBy('Wo_Products.id')
            ->paginate($limit);
        }

        return response()->json(['error' => 'Some Of Value Are Invalid..'], 404);

    }


    public function search($search, $limit = null)
    {
        $user_id = $this->user_id;

        if(!empty($search) AND !empty($limit) AND is_numeric($limit)){
            return DB::table('Wo_Custom_Store')
            ->distinct()
            ->select(
                'Wo_Products.id', 
                'Wo_Custom_Store.user_id',
                'Wo_Langs.english',
                'Wo_Products_Media.image', 
                'Wo_Custom_Store.logo', 
                'Wo_Custom_Store.banner', 
                'Wo_Products.name', 
                'Wo_Products.description', 
                'Wo_Products.category', 
                'Wo_Products.price', 
                'Wo_Products.remark', 
                'Wo_Products.expire'
            )
            ->leftJoin('Wo_Products', 'Wo_Custom_Store.user_id', '=', 'Wo_Products.user_id')
            ->leftJoin('Wo_Service_Categories', 'Wo_Products.category', '=', 'Wo_Service_Categories.id')
            ->leftJoin('Wo_Products_Categories', 'Wo_Products.category', '=', 'Wo_Products_Categories.id')

            ->leftJoin('Wo_Langs', function($q) {
                $q->on('Wo_Products_Categories.lang_key', '=', 'Wo_Langs.id');
                $q->orOn('Wo_Langs.id', '=', 'Wo_Service_Categories.lang_key');
            })

            ->leftJoin('Wo_Products_Media', 'Wo_Products.id', '=', 'Wo_Products_Media.product_id')
            ->where(['Wo_Custom_Store.user_id' => $user_id, 'Wo_Custom_Store.status' => 1])
            ->where('Wo_Products.name', 'like', '%' . $search . '%')
            ->groupBy('Wo_Products.id')
            ->paginate($limit);
        }

        return response()->json(['error' => 'Some Of Value Are Invalid..'], 404);
    }

    public function color()
    {
        $user_id = $this->user_id;

        $color = DB::table('Wo_Cs_Themes_Colors')->where('user_id', $user_id)->first();
        return response()->json($color);
    }

    public function pages()
    {
        $user_id = $this->user_id;

        $pages = DB::table('Wo_Cs_Generated_Pages')->where('user_id', $user_id)->get();
        return response()->json($pages);
    }

    public function meta()
    {
        $user_id = $this->user_id;

        $meta = DB::table('Wo_Cs_Meta')->where('user_id', $user_id)->first();
        return response()->json($meta);
    }


}
