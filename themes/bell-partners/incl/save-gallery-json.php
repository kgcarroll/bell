<?php 



function save_gallery_json($post_id){
  if (get_page_template_slug($post_id) == 'page-templates/gallery.php'){
    $gallery = array();
    $category = array();
    $videos = array();
    $tours = array();


    // Get Images
    if ($images = get_field('image_gallery',$post_id)){
      // Categories
      foreach($images as $row){
        $content = array();
        if (!empty($row['content'])){
          // echo '<pre>';print_r($row['content']); echo '</pre>'; die;
          foreach ($row['content'] as $row2){
            $content[] = array(
              'type' => 'image',
              'slider' => $row2['sizes']['slider'],
              'slider_width' => $row2['sizes']['slider-width'],
              'full' => $row2['url'],
              'full_width' => $row2['width'],
              'slider_thumbnail' => $row2['sizes']['slider_thumbnail'],
              'featured' => $row2['sizes']['masonry_featured'],
              'thumbnail_soft' => $row2['sizes']['masonry_thumb_softcrop'],
              'thumbnail_hard' => $row2['sizes']['masonry_thumb_hardcrop'],
              'featured_width' => $row2['sizes']['masonry_featured-width'],
              'thumbnail_soft_width' => $row2['sizes']['masonry_thumb_softcrop-width'],
              'thumbnail_hard_width' => $row2['sizes']['masonry_thumb_hardcrop-width'],
              'columns' => get_field('featured_image', $row2['ID']),
              'uncropped' => get_field('resize_image', $row2['ID']),
              'caption' => $row2['caption'],
              'alt' => $row2['alt']
            );
          }
        }
        $category[] = array (
          'category_name' => $row['category_name'],
          'content' => $content
        );
      }
    }

    // Get Videos
    if($videosData = get_field('videos')) {
      // echo '<pre>';print_r($videosData); echo '</pre>'; die;
      foreach($videosData as $row){
        $videos[] = array (
          'content' => $row['content'],
          'caption' => $row['caption'],
          'thumbnail_hard' => $row['preview_image']['sizes']['masonry_thumb_hardcrop'],
          'thumbnail_hard_width' => $row['preview_image']['sizes']['masonry_thumb_hardcrop-width'],
          'featured' => $row['preview_image']['sizes']['masonry_featured'],
          'featured_width' => $row['preview_image']['sizes']['masonry_featured-width'],
        );
      }
    }


    // Get Tours
    if($toursData = get_field('360_tour')) {      
      // echo '<pre>';print_r($toursData); echo '</pre>'; die;
      foreach($toursData as $row){
        $tours[] = array (
          'content' => $row['360_tour'],
          'caption' => $row['caption'],
          'thumbnail_hard' => $row['preview_image']['sizes']['masonry_thumb_hardcrop'],
          'thumbnail_hard_width' => $row['preview_image']['sizes']['masonry_thumb_hardcrop-width'],
          'featured' => $row['preview_image']['sizes']['masonry_featured'],
          'featured_width' => $row['preview_image']['sizes']['masonry_featured-width'],
        );
      }
    }


    // Build Final Array
    $gallery = array(
      'gallery_type' => get_field('gallery_type'),
      'slider_thumbnails' => get_field('include_slider_thumbnails'),
      'image_gallery' => $category,
      'videos' => $videos,
      'tours' => $tours
    );

    file_put_contents(get_template_directory() . '-child/JSON/gallery.json',json_encode($gallery));
  }
}
add_action( 'save_post', 'save_gallery_json' );