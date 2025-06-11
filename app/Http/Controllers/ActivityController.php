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
                'club_id'=>$req->club_id,
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
            $serche = $req->query('search');
            $perPage = $req->query('per_page', 12);  // Valeur par défaut à 10
            $page = $req->query('page', 1); // Valeur par défaut à la page 1

            if(!empty($serche)){
                $tab = Activity::where("title", "like", "%$serche%")
                    ->orWhere("description", "like", "%$serche%")
                    ->paginate($perPage, ['*'], 'page', $page);
            } else {
                $tab = Activity::orderBy('id', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
            }

            // Formatage des images
            foreach($tab as $ele) {
                $images = json_decode($ele->images);
                $pthe = [];
                foreach($images as $image) {
                    $pthe[] = asset($image);
                }
                $ele->images = $pthe;
            }

            if($tab->isEmpty()) {
                return response()->json(["Message" => "No activity found"]);
            } else {
                return response()->json($tab);
            }

        } catch (Exception $e) {
            return response()->json([
                "message" => "Something went wrong!",
                "error" => $e->getMessage()
            ]);
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
                return response()->JSON($tab);
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
    public function clubActivity($id, Request $req)
    {
        try {
            $perPage = $req->query('per_page', 12);  // Default 12 per page
            $page = $req->query('page', 1); // Default page 1

            if (!is_numeric($id)) {
                return response()->json(["message" => "Invalid club ID"]);
            }

            $activity = Activity::where('club_id', $id)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format image URLs
            $activity->getCollection()->transform(function ($item) {
                if ($item->images) {
                    $decodedImages = json_decode($item->images);
                    if (is_array($decodedImages)) {
                        $item->images = array_map(fn($img) => asset($img), $decodedImages);
                    }
                }
                return $item;
            });

            return response()->json($activity);

        } catch (Exception $e) {
            return response()->json([
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ]);
        }
    }


}


