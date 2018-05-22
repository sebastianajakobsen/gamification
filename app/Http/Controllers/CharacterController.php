<?php

namespace App\Http\Controllers;



use App\Character;
use App\Http\Requests\CharacterRequest;
use App\Quest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use App\Achievements\UserMadeACharacter;

class CharacterController extends Controller
{

    // Create a new thumbnail file for images
    public function createThumbnailfromFile($width, $height, $orignalfile)
    {
        $upload = Image::make($orignalfile->getRealPath())->fit($width, $height, function ($c) {
            $c->aspectRatio();
        });
        $upload->encode('jpg');
        return $upload;
    }


    // Show all characters
    public function index()
    {
        return view('characters.index');
    }


    // Show specific character
    public function show($name)
    {
        $character = Character::where('name', '=', $name)->firstOrFail();
        if ($character) {
            return view('characters.show', compact('character'));
        }
    }


    public function create()
    {
        return view('characters.create');
    }

    public function store(CharacterRequest $request)
    {
        $character = new Character;
        $character->name = $request->name;
        $character->user_id = Auth::user()->id;
        $character->hit_points = 250;
        $character->total_hit_points = 250;

        if ($request->file('image')) {
            $image = md5($request->name . microtime());
            $imageFilename = $image . ".jpg";
            $file = @file_get_contents($request->image);
            $imageSaved = Storage::disk(env('STORAGE_DISK_DRIVER'))->put('characters/' . $imageFilename, $file,
                [
                    'visibility' => 'public',
                    'CacheControl' => 'max-age=31536000'
                ]);

            if ($imageSaved) {
                $imageFilename_sm = $image . '_sm.jpg';
                $file = $this->createThumbnailfromFile(80, 80, $request->file('image'));
                $imageSmSaved = Storage::disk(env('STORAGE_DISK_DRIVER'))->put('characters/' . $imageFilename_sm, $file,
                    [
                        'visibility' => 'public',
                        'CacheControl' => 'max-age=31536000'
                    ]);
            }

        }
        if ($imageSaved && $imageSmSaved) {
            $character->image = $imageFilename;
            $character->image_sm = $imageFilename_sm;


            if ($character->save()) {


                $details = Auth::user()->achievementStatus(new UserMadeACharacter());

                if($details->unlocked_at === null) {
                    // unlocking achivement for user for creating an accont
                    Auth::user()->unlock(new UserMadeACharacter());
                    return redirect()->route('home', ['slug' => Auth::user()->name])->with('achivement', 'User got a new Achivement');
                } else {
                    return redirect()->route('home', ['slug' => Auth::user()->name])->with('flash_message', $character->name . ' er blevet oprettet!');
                }


            }
        }

    }

}
