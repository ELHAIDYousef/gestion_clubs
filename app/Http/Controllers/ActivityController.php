<?php

namespace App\Http\Controllers;
use App\Models\Activity;
use Exception;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Notifications\Action;

class ActivityController extends Controller
{
    // cette function a comme objective 
    public function store(Request $req){
        try{
            $images=$req->file('image');
            $paths=[];
            if($images){
                foreach($images as $image){
                    $patheName=time() . '_' . uniqid() . '.' .$image->getClientOriginalExtension();
                    $image->move(public_path('Activity'),$patheName);
                    $path='Activity/'.$patheName;
                    $paths[]=$path;
                }   
            }
            $jsonImage=json_encode($paths);
            $tab=Activity::create([
                'club_id'=>$req->id_club,
                'title'=>$req->title,
                'description'=>$req->description,
                'images'=>$jsonImage
            ]);
            return response()->JSON(["the Activity create"=>$tab]);

        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng!",
                                     "errer"=>$e->getMessage()]);
        }
       

    }
    //cette fonction pour obtuner les 10 dernier Activite
    public function index(Request $req){
        try{
            $serche=$req->query('searche');
            if(!empty($serche)){
                $tab=Activity::where("title","like","%$serche%")->orWhere("description","like","%$serche%")->get();
                foreach($tab as $ele){
                    $images=json_decode($ele->images);
                    $pthe=[];
                    foreach($images as $image){
                        $pthe[]=asset($image);
                    }
                    $ele->images=$pthe;
                }
            }
            else{
                $tab=Activity::orderby("id","desc")->limit(10)->get();
                foreach($tab as $ele){
                    $images=json_decode($ele->images);
                    $pthe=[];
                    foreach($images as $image){
                        $pthe[]=asset($image);
                    }
                    $ele->images=$pthe;
                }
            }
            if(count($tab)==0){
                return response()->JSON(["Message"=>"Note found"]);    
            }else{
                return response()->json(["Last 10 Activities"=>$tab]);;
            }
            
            
        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng!",
                                     "errer"=>$e->getMessage()]);
        }
        
    }
    //cette fonction pour obtuner un activite
    public function show($id){
        try{
            if(is_numeric($id)){
                $tab=Activity::find($id);
                $images=json_decode($tab->images);
                $pathe=[];
                foreach($images as $image){
                    $pathe[]=asset($image);
                }
                $tab->images=$pathe;
                return response()->JSON(["the Activity ".$tab['title']=>$tab]);
            } else{
                return response()->JSON(["message"=>"Activity does not exist."]);
            }
        }catch(Exception $e){
            return response()->JSON(["message"=>"Activity does not exist.",
                                     "errer"=>$e->getMessage()]);
        }
        
    }
    // cette fonction permet de supremer les activity
    public function destroy($id){
        try{
            if(is_numeric($id)){
                $element=Activity::find($id);
                $images=json_decode($element->images);
                foreach($images as $image){
                    if(file_exists(public_path($image))){
                        unlink(public_path($image));
                    }
                }
                $element->delete();
                return response()->JSON(["message"=>"Delete sucssufly !",
                                        "Activity"=>$element]);
            }  

        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng!",
                                     "errer"=>$e->getMessage()]);
        }
    }
    // cette fonction pour modifier un activite
    public function update(Request $req, $id){
        try{
            if(is_numeric($id)){
                $element=Activity::find($id);
                // suppremer tous les images
                $images=json_decode($element->images);
                foreach($images as $image){
                    if(file_exists(public_path($image))){
                        unlink(public_path($image));
                    }
                }
                // prendre les image
                $images=$req->file('image');
                $paths=[];
                    if($images){
                        foreach($images as $image){
                            $patheName=time() . '_' . uniqid() . '.' .$image->getClientOriginalExtension();
                            $image->move(public_path('Activity'),$patheName);
                            $path='Activity/'.$patheName;
                            $paths[]=$path;
                        }   
                    }
                $jsonImage=json_encode($paths);
                $element->title=$req->title;
                $element->description=$req->description;
                $element->images=$jsonImage;
                $element->save();
                return response()->JSON(["Updet sucssafly"=>$element]);
            }else{
                return response()->JSON(["message"=>"Something went worng!"]);
            }
            
        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng!",
                                     "erre"=>$e->getMessage()

            ]);
        }
        

    }
    // cette fonction pour donne les activiti d'un club spécifique
    public function clubActivity($id){
        try{
            if(is_numeric($id)){
                $activty=Activity::select('activities.*')
                                            ->where('club_id',$id)
                                            ->orderBy('id','desc')
                                            ->limit(10)
                                            ->get();
                return response()->JSON(['the last activite of clubs '.Club::find($id)->name=>$activty]);
            }else{
                return response()->JSON(["message","Something went worng"]);
            }
            
        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng",
                                      "errer"=>$e->getMessage()
                                    ]);
        }
        
    }
}


