Commands to start the script :

php css_generator.php img

OPTIONS :

-r, --recursive
Look for images into the assets_folder passed as arguement and all of its subdirectories.

-i, --output-image=IMAGE
Name of the generated image. If blank, the default name is « sprite.png ».

-s, --output-style=STYLE
Name of the generated stylesheet. If blank, the default name is « style.css »

EXEMPLE :

php css_generator.php -r img
php css_generator.php --output-image=MYSPRITE img
