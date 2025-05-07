<?php

use App\Http\Controllers\About\AboutController;
use App\Http\Controllers\Assets\CategoryController;
use App\Http\Controllers\Assets\ColorsController;
use App\Http\Controllers\Assets\SizeController;
use App\Http\Controllers\Assets\SubCategoryController;
use App\Http\Controllers\Assets\TagController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Footer\FooterController;
use App\Http\Controllers\Home\ContactController;
use App\Http\Controllers\Home\SectionOneController;
use App\Http\Controllers\Home\SectionThreeController;
use App\Http\Controllers\Home\SectionTwoController;
use App\Http\Controllers\Home\SliderController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'createUser']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/sliders', [SliderController::class, 'getAllSliders']);
Route::get('/sliders/{id}', [SliderController::class, 'getSliderById']);

Route::get('/section-one', [SectionOneController::class, 'getAllSectionOne']);
Route::get('/section-one/{id}', [SectionOneController::class, 'getSectionOneById']);

Route::get('/section-two', [SectionTwoController::class, 'getAllSectionTwo']);
Route::get('/section-two/{id}', [SectionTwoController::class, 'getSectionTwoById']);


Route::get('/section-three', [SectionThreeController::class, 'getAllSectionThree']);
Route::get('/section-three/{id}', [SectionThreeController::class, 'getSectionThreeById']);

Route::get('/contacts', [ContactController::class, 'getAllContact']);
Route::get('/contacts/{id}', [ContactController::class, 'getContactById']);

Route::get('/about', [AboutController::class, 'getAllAbout']);
Route::get('/about/{id}', [AboutController::class, 'getAboutById']);

Route::get('/footer', [FooterController::class, 'getAllFooters']);
Route::get('/footer/{id}', [FooterController::class, 'getFooterById']);

Route::get('/categories', [CategoryController::class, 'getAllCategories']);
Route::get('/categories/{id}', [CategoryController::class, 'getCategoryById']);

Route::get('/sub-categories', [SubCategoryController::class, 'getAllSubCategories']);
Route::get('/sub-categories/{id}', [SubCategoryController::class, 'getSubCategoryById']);

Route::get('/colors', [ColorsController::class, 'getAllColors']);
Route::get('/colors/{id}', [ColorsController::class, 'getColorById']);

Route::get('/sizes', [SizeController::class, 'getAllSizes']);
Route::get('/sizes/{id}', [SizeController::class, 'getSizeById']);

Route::get('/tags', [TagController::class, 'getAllTags']);
Route::get('/tags/{id}', [TagController::class, 'getTagById']);


// Protected routes 
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/user/{id}', [AuthController::class, 'deleteUser']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/user/email/{email}', [AuthController::class, 'getUserByEmail']);
    Route::get('/user/id/{id}', [AuthController::class, 'getUserById']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/sliders', [SliderController::class, 'createSlider']);
    Route::put('/sliders/{id}', [SliderController::class, 'updateSlider']);
    Route::delete('/sliders/{id}', [SliderController::class, 'deleteSlider']);

    Route::post('/section-one', [SectionOneController::class, 'createSectionOne']);
    Route::put('/section-one/{id}', [SectionOneController::class, 'updateSectionOne']);
    Route::delete('/section-one/{id}', [SectionOneController::class, 'deleteSectionOne']);
    Route::delete('/section-one', [SectionOneController::class, 'deleteAllSectionOne']);

    Route::post('/section-two', [SectionTwoController::class, 'createSectionTwo']);
    Route::put('/section-two/{id}', [SectionTwoController::class, 'updateSectionTwo']);
    Route::delete('/section-two/{id}', [SectionTwoController::class, 'destroy']);

    Route::post('/section-three', [SectionThreeController::class, 'createSectionThree']);
    Route::put('/section-three/{id}', [SectionThreeController::class, 'updateSectionThree']);
    Route::delete('/section-three/{id}', [SectionThreeController::class, 'deleteSectionThree']);

    Route::post('/contacts', [ContactController::class, 'createContact']);
    Route::put('/contacts/{id}', [ContactController::class, 'updateContact']);
    Route::delete('/contacts/{id}', [ContactController::class, 'deleteContact']);

    Route::post('/about', [AboutController::class, 'createAbout']);
    Route::put('/about/{id}', [AboutController::class, 'updateAbout']);
    Route::delete('/about/{id}', [AboutController::class, 'deleteAbout']);


    Route::post('/footer', [FooterController::class, 'createFooter']);
    Route::put('/footer/{id}', [FooterController::class, 'updateFooter']);
    Route::delete('/footer/{id}', [FooterController::class, 'deleteFooter']);

    Route::post('/categories', [CategoryController::class, 'createCategory']);
    Route::put('/categories/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);

    Route::post('/sub-categories', [SubCategoryController::class, 'createSubCategory']);
    Route::put('/sub-categories/{id}', [SubCategoryController::class, 'updateSubCategory']);
    Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'deleteSubCategory']);

    Route::post('/colors', [ColorsController::class, 'createColor']);
    Route::put('/colors/{id}', [ColorsController::class, 'updateColor']);
    Route::delete('/colors/{id}', [ColorsController::class, 'deleteColor']);

    Route::post('/sizes', [SizeController::class, 'createSize']);
    Route::put('/sizes/{id}', [SizeController::class, 'updateSize']);
    Route::delete('/sizes/{id}', [SizeController::class, 'deleteSize']);

    Route::post('/tags', [TagController::class, 'createTag']);
    Route::put('/tags/{id}', [TagController::class, 'updateTag']);
    Route::delete('/tags/{id}', [TagController::class, 'deleteTag']);
});

