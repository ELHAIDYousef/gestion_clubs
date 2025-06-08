<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Club;
use function Laravel\Prompts\error;

class EventsController extends Controller{
    // cette fonctionn permmet de créer un Evenemrnt
    public function store(Request $req){
        try{
            // pour stoke l'image réelement
            $uploadImage = $req->file('image');
            // vérify est ce que l'image n'est pas nulle
            if ($uploadImage) {
                    // pour obtenu le nom de l'image et sont extention
                    $uploadImageName =time() . '_' . uniqid() . '.' .$uploadImage->getClientOriginalExtension();
                    // stoker cette image en le dosser Events qui existe en public
                    $uploadImage->move(public_path('Events'), $uploadImageName);
                    // crée l'evenement on le basse de donne et bien sur en stoke l'image le chemis de l'image
                    $pathimage='Events/'.$uploadImageName;
                    $event=Announcement::create([
                        'club_id'=>$req->club_id,
                        'title'=>$req->title,
                        'description'=>$req->description,
                        'image'=>$pathimage,
                    ]);
                    return response()->json([
                        'message' => 'Event created successfully',
                        'Event' => $event,
                    ],201);
            }
        }catch(Exception $e){
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(), // Optional: Include error details in development
            ], 500);
            }
    }
    // cette  fonction permmet de liste tous les 10 evenement dernier
    public function index(Request $req){
        try{
            $serche = $req->query('search');
            $perPage = $req->query('per_page', 12);  // Valeur par défaut à 10
            $page = $req->query('page', 1); // Valeur par défaut à la page 1

            if(!empty($serche)){
                $tab = Announcement::where("title", "like", "%$serche%")
                    ->orWhere("description", "like", "%$serche%")
                    ->paginate($perPage, ['*'], 'page', $page);
            } else {
                $tab = Announcement::orderBy('id', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                foreach($tab as $ele) {
                    $ele->image = asset($ele->image);
                }
            }

            if($tab->isEmpty()){
                return response()->json(["Message" => "No announcements found"]);
            } else {
                return response()->json(["Last announcements" => $tab]);
            }

        } catch (Exception $e) {
            return response()->json([
                "message" => "Something went wrong!",
                "error" => $e->getMessage()
            ]);
        }
    }

    // cette  fonction permmet return un evenement en utilisen sont id
    public function show($id){
        try{
            $tab=Announcement::find($id);
            $tab->image=asset($tab->image);
            return response()->JSON(["le ". $tab['title']." est le dérnire event"=>$tab]);
        }catch(Exception $e){
            return response()->JSON(["message"=>"Something went worng!",
                                     "errer"=>$e->getMessage()]);
        }
    }
    // et pour modifier sur un evenement
    public function update(Request $req,$id){
        try{
            $event=Announcement::find($id);
            $imag=$req->file('image');
            $uploadImageName=time()."_".uniqid().".".$imag->getClientOriginalExtension();

            $pathimage='Events/'.$uploadImageName;
            // le lighne de l'image pricident
            if(!empty($event->image)){
                $filePath = public_path($event->image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            // modifier les las attrube de l'annece
            $imag->move(public_path('Events'),$uploadImageName);
            $event->title=$req->input('title');
            $event->description=$req->input('description');
            $event->image=$pathimage;
            $event->save();
            return response()->JSON(["mesage"=>"Update successful!",
                                      "event"=>$event]);
        }catch(Exception $e){
            return response()->JSON(["mesage"=>'Something went wrong!',
                                     "errer"=>$e->getMessage()]);
        }

    }
    // et pour suppremer sur un evenement
    public function destroy($id){
        try{
            $event=Announcement::find($id);
            $imagefile=public_path($event->image);
            if(file_exists($imagefile)){
                unlink($imagefile);
            }
            $event->delete();
            return response()->JSON(["mesage"=>"Delete successful!",
                                      "event"=>$event]);
        }catch(Exception $e){
            return response()->JSON(["mesage"=>'Something went wrong!',
                                     "errer"=>$e->getMessage()]);
        }

    }
    // pour afficher les event d'un club spicifique
    public function clubEvent($id, Request $req){
        try{
            $perPage = $req->query('per_page', 12);  // Valeur par défaut à 10
            $page = $req->query('page', 1); // Valeur par défaut à la page 1

            $activty = Announcement::select('Announcements.*')
                ->where('club_id', $id)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                $activty
            ]);

        } catch (Exception $e) {
            return response()->json([
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ]);
        }
    }



}
