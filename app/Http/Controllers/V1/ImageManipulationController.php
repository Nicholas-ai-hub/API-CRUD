<?php

namespace App\Http\Controllers\V1;

use File;
use Illuminate\Support\Str;
use App\Models\ImageManipulation;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageManipulationRequest;
use App\Http\Requests\UpdateImageManipulationRequest;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageManipulationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(StoreImageManipulationRequest $request)
    {
        $all = $request->all();

        //სურათის ასატვირთად გაწერილი ვალიდაცია
        /** @var UploadedFile|string $image */
        $image = $all['image'];
        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => null
        ];

        if(isset($all['album_id'])){
            //todo useristvis

            $data['album_id'] = $all['album_id'];
        }

        //დირექტორიის შექმნა
        $dir = 'images/'. Str::random(). '/';
        $absolutePath = public_path($dir);
        if(!File::exists($absolutePath)){
            File::makeDirectory($absolutePath, 0755, true);
        }
        //ატვირთული სურათის ფოლდერში შენახვა
        if($image instanceof UploadedFile){
            $data['name'] = $image->getClientOriginalName();
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $originalPath = $absolutePath . $data['name'];
            $image->move($absolutePath, $data['name']); 
            
        }
        //სურათის გადმოსაწერად
        else{
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image,PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $originalPath = $absolutePath . $data['name'];

            copy($image, $originalPath);
        }
        $data['path'] = $dir . $data['name'];
        $w = $all['w'];
        $h = $all['h'] ?? false;
        
        list($width, $height) = $this->getWidthAndHeight($w, $h, $originalPath);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $imageManipulation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateImageManipulationRequest  $request
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateImageManipulationRequest $request, ImageManipulation $imageManipulation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $imageManipulation)
    {
        //
    }

    //სურათის ზომების შეცვლისთვის ვქმნით ახალ ფუნქციას
    protected function getWidthandHeight($w, $h, string $originalPath){
        $image = Image::make($originalPath);
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        
        if(str_ends_with('w', '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;
            
            $newWidth = $originalWidth * $ratioW / 100;
             $newHeight = $originalHeight * $ratioH / 100;
        }
        else{
            $newWidth = (float)$w;
            /**
            * $originalWidth - $newWidth
            * $originalHeight - $newHeight
            * -------------------------
            * $newHeight = $originalHeight * $newWidth/$originalWidth
            */
            $newHeight = $h ? (float)$h : ($originalHeight * $newWidth/$originalWidth);
        }
        
        return [$newWidth, $newHeight];
    }
}
