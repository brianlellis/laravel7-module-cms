<?php

// CMS PAGES (LARABERG)

Route::group(['prefix' => 'filemanager', 'middleware' => ['auth', 'verified', 'web']], function () {
  \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::name('cms.')->prefix('/api/cms/')->middleware(['auth', 'verified'])->group(function () {
  $perm   = "permission:cms-";
  $rapyd  = "\Rapyd\Rapyd";
  Route::name('page.')->prefix('page/')->group(function() use ($perm, $rapyd) {
    Route::post('store', "{$rapyd}Page@store_content")->name('store')->middleware("{$perm}page-create");
    Route::get('delete/{content_id}', "{$rapyd}Page@delete")->name('delete')->middleware("{$perm}page-delete");
  });

  Route::name('blog.')->prefix('blog/')->group(function() use ($perm, $rapyd) {
    Route::post('store',         "{$rapyd}Blog@store")->name('store');
    Route::post('{blog}/update', "{$rapyd}Blog@update")->name('update');
    Route::post('{blog}/delete', "{$rapyd}Blog@delete")->name('delete')->middleware("{$perm}blog-delete");
  });

  Route::name('category.')->prefix('category/')->group(function() use ($perm, $rapyd) {
    Route::post('store',             "{$rapyd}Category@store")->name('store')
      ->middleware("{$perm}category-create");
    Route::post('{category}/update', "{$rapyd}Category@update")->name('store')
      ->middleware("{$perm}category-update");
    Route::post('{category}/delete', "{$rapyd}Category@delete")->name('delete')
      ->middleware("{$perm}category-delete");
  });

  Route::name('wrapper.')->prefix('wrapper/')->group(function() use ($perm, $rapyd) {
    Route::post('{wrapper}/delete', "{$rapyd}Wrapper@delete")->name('delete');
    Route::post('store',            "{$rapyd}Wrapper@store")->name('store');
    Route::post('{wrapper}/update', "{$rapyd}Wrapper@update")->name('store');
    Route::get('{wrapper}',         "{$rapyd}Wrapper@get")->name('get');
  });
});
