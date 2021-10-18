<?php
/**
* Plugin Name: Valoraciones de películas
* Description: Esto agregará una opción de estrellas para valorar películas
* shortcode [star_rating_form]
* Author: Nelly González
* Author URI: https://github.com/NellyGonzalezB/REG003-wordpress-plugin
**/

register_activation_hook(__FILE__, 'Star_Aspirante_init');

function Star_Aspirante_init(){

    global $wpdb;
    $tabla_aspirantes = $wpdb->prefix . 'aspirante';
    $charset_collate = $wpdb->get_charset_collate();
    // Prepara la consulta
    $query = "CREATE TABLE IF NOT EXISTS $tabla_aspirantes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        comment varchar(100) NOT NULL,
        estrellas smallint(5) NOT NULL,
        aceptacion smallint(4) NOT NULL,
        created_at datetime NOT NULL,
        UNIQUE (id)
        ) $charset_collate;";
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($query); // Lanza la consulta para crear la tabla de manera segura
    }
// Define el shortcode que imprime
add_shortcode('star_rating_form', 'Star_Rating_form');

/**
 * Crea el shortcode
 * 
 * @return void
 */

function Star_Rating_form()
{
    
    global $wpdb;
    $tabla_form_estrella = $wpdb->prefix . 'aspirante';
    $estrellas_aspirante = $wpdb->get_results("SELECT * FROM $tabla_form_estrella");
    if (!empty($_POST)
        AND $_POST['comment'] != ''
        AND $_POST['aceptacion'] == '1'
        AND wp_verify_nonce($_POST['aspirante_nonce'], 'graba_aspirante')
    ) {
        $tabla_aspirantes = $wpdb->prefix . 'aspirante'; 
        $comment = sanitize_text_field($_POST['comment']);
        $estrellas = sanitize_text_field($_POST['estrellas']);
        $aceptacion = (int)$_POST['aceptacion'];
        $created_at = date('Y-m-d H:i:s');
        $wpdb->insert(
            $tabla_aspirantes,
            array(
                'comment' => $comment,
                'estrellas' => $estrellas,
                'aceptacion' => $aceptacion,
                'created_at' => $created_at,
            )
        );
        echo "<p class='exito'><b>Tu comentario ha sido registrado</b>. Gracias 
            por tu interés <p>";
    }

    wp_enqueue_style('css_aspirante', plugins_url('style.css', __FILE__));
    ob_start();
    ?>
    <form action="<?php get_the_permalink(); ?>" method="post" id="form_aspirante"
class="valoraciones">
        <?php wp_nonce_field('graba_aspirante', 'aspirante_nonce'); ?>
        <div class="rating-title">
            <h3>Valoración de Películas</h3>
        </div>
        <div class="rating-input">
            <label for="comment">Comentario</label>
            <textarea type="text" name="comment" id="comment" required></textarea>
        </div>
        <div class="rate" id="estrellas" required>
            <input name="estrellas" type="radio" id="star5" value="5" />
            <label for="star5" title="text">5 stars</label>
            <input name="estrellas" type="radio" id="star4" value="4" />
            <label for="star4" title="text">4 stars</label>
            <input name="estrellas" type="radio" id="star3" value="3" />
            <label for="star3" title="text">3 stars</label>
            <input name="estrellas" type="radio" id="star2" value="2" />
            <label for="star2" title="text">2 stars</label>
            <input name="estrellas" type="radio" id="star1" value="1" />
            <label for="star1" title="text">1 star</label>
        </div><br>
       <?php 
       foreach ($estrellas_aspirante as $configuration) {
        $estrellas = $configuration->estrellas;
        $total += $estrellas;
        $i++;
        
       } 
       //echo " <h4>Total estrellas:$total</h4>";
       echo "<h4>Total de votantes:$i</h4>";
       $promedio = intval($total/$i);
       echo "<h4>Promedio de estrellas:$promedio</h4>"
       ?>
        <div class="form-input">
            <br><label for="aceptacion">La información facilitada se tratará 
            con respeto y admiración.</label>
            <br><input type="checkbox" id="aceptacion" name="aceptacion"
                value="1" required> Entiendo y acepto las condiciones
        </div>
        
        <div class="rating-input">
            <input type="submit" value="Enviar">
        </div>
    </form>
    <?php
    return ob_get_clean();
}