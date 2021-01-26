<?php  
  
namespace App\Http\Controllers;  
  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Redis;  
  
class ProductController extends Controller  
{  
      public function __construct()  
      {  
        //$this->middleware('auth');  
      }  
      
      public function create()  
      {  
         return view('products.create');  
      }
      
      public function store(Request $request)  
      {  
         $tags = explode(',',$request->get('tags'));  
          $productId = self::getProductId();  
      
          if(self::newProduct($productId, [
          'name' => $request->get('product_name'),
          'image' => $request->get('product_image'),
          'product_id' => $productId
          ])){  
          //self::addToTags($tags);  
          //self::addToProductTags($productId, $tags);  
          //self::addProductToTags($productId, $tags);  
         }  
         
         return redirect()->route('product.all');  
     }  
     
      public function viewProducts(Request $request)  
    {  
          
      if($request->has('tag')){  
      $products = self::getProductByTags(($request->get('tag')));  
     }else{  
      $products = self::getProducts();  
     }  
      //$tags = Redis::sMembers('tags');  
          $tags = [];
      return view('products.browse')->with(['products' => $products, 'tags' => $tags]);  
    } 
     
     /*
     * Increment product ID every time
     * a new product is added, and return
     * the ID to be used in product object
     */
      static function getProductId()  
      {  
          if(!Redis::exists('product_count')){
           Redis::set('product_count',0);     
          } 
           
           
          return Redis::incr('product_count');  
     }  
     
     /*
     * Create a hash map to hold a project object
     * e.g HMSET product:1 product "men jean" id 1 image "img-url.jpg" 
     * Then add the product ID to a list hold all products ID's
     */
     static function newProduct($productId, $data)  
     {  
          self::addToProducts($productId);  
            $val = Redis::hgetall("product");
          //echo "<pre>";print_r($val);echo "</pre>";
          $val2 = Redis::hmset("product:$productId", $data);  
         if($val2){
             //$val3 = Redis::hgetall("product:17");
             return 1;
         }
     }  
     
     /*
     * A Ordered Set holding all products ID with the
     * PHP time() when the product was added as the score
     * This ensures products are listed in DESC when fetched
     */
     static function addToProducts($productId) : void  
     {  
         $val = Redis::ZRANGE('products',0,-1,'WITHSCORES');
         //echo "<pre>";print_r($val);echo "</pre>";
          Redis::zAdd('products', time(), $productId);  
     } 
     
     /*
     * A unique Sets of tags
     */
     static function addToTags(array $tags)  
     {  
         Redis::sAddArray('tags',$tags);  
     }  
     
     /*
     * A unique set of tags for a particular product
     * eg SADD product:1:tags jean men pants 
     */
     static function addToProductTags($productId, $tags)  
     {  
         Redis::sAddArray("product:$productId:tags",$tags);  
     }  
     
     /*
     * A List of products carry this particular tag
     * ex1 RPUSH men 1 3
     * ex2 RPUSH women 2 4 
     */
     static function addProductToTags($productId, $tags)  
     {  
         foreach ($tags as $tag){  
          Redis::rPush($tag,$productId);  
         } 
     }  
     
    /*  
    * In a real live example, we will be returning 
    * paginated data by calling the lRange command 
    * lRange start end 
    */  
    static function getProducts($start = 0, $end = -1) : array  
    {  
          $productIds = Redis::zRange('products', $start, $end, 'WITHSCORES');  
        
          $products = [];  
      
          foreach ($productIds as $productId => $score) 
          {  
              $products[$score]= Redis::hGetAll("product:$productId");  
          } 
          
          return $products;  
     }
    
    static function getProductByTags($tag, $start = 0, $end = -1) : array  
    {  
      $productIds = Redis::lRange($tag, $start, $end);  
      $products = [];  

      foreach ($productIds as $productId) {  
      $products[] = Redis::hGetAll("product:$productId");  
     }  
      return $products;  
    }
    
    public function deleteProducts($id){
        $val3 = Redis::hgetall("product:$id");
        $check = Redis::del("product:$id");
        $check3 = Redis::zscore("products",$id);
        $check2 = Redis::zrem("products");
        return redirect()->route('product.all'); 
    }
    
    public function deleteAllProducts(){
        
        $keys = Redis::keys('product:*');
        
        $clients = [];
        foreach ($keys as $key) {
            $productId = explode(':',$key)[1];
            $check = Redis::del("product:$productId");
        }
        echo "<pre>";print_r($clients);die;
        die;
        die;
        $productIds = Redis::zRange('products', 0, -1, 'WITHSCORES');
        echo "<pre>";print_r($productIds);echo "</pre>";die;
        foreach($productIds as $productId => $score){
            echo $productId."<br>";
            Redis::zrem("products",$productId);
        }
        echo "<pre>";print_r($productIds);echo "</pre>";
        $productIds2 = Redis::zRange('products', 0, -1, 'WITHSCORES');
        echo "<pre>";print_r($productIds2);echo "</pre>";die;
    }
 
 }