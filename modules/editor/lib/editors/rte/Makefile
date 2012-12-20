EDITOR = ./assets/editor
EDITOR_UNCOMPRESSED = ./assets/editor-uncompressed

main:
	@cat ./mooeditable/Source/MooEditable/MooEditable.js \
	./mooeditable/Source/MooEditable/MooEditable.UI.MenuList.js \
	./mooeditable/Source/MooEditable/MooEditable.Extras.js \
	./mooeditable/Source/MooEditable/MooEditable.Paste.js \
	./mooeditable/Source/MooEditable/MooEditable.CleanPaste.js \
	./mooeditable/Source/MooEditable/MooEditable.Outline.js \
	./mooeditable/Source/MooEditable/MooEditable.Image.js \
	./build/stub.js \
	> ${EDITOR_UNCOMPRESSED}.js

	@cat ./mooeditable/Assets/MooEditable/MooEditable.css \
	./mooeditable/Assets/MooEditable/MooEditable.Image.css \
	./mooeditable/Assets/MooEditable/MooEditable.Extras.css \
	./mooeditable/Assets/MooEditable/MooEditable.SilkTheme.css \
	./mooeditable/Assets/MooEditable/MooEditable.Paste.css \
	> ${EDITOR_UNCOMPRESSED}.css
	
	@cp ./mooeditable/Assets/MooEditable/icons.png ./assets/icons.png
	@cp ./mooeditable/Assets/MooEditable/images/mooeditable-toolbarbuttons-silk.png ./assets/images/mooeditable-toolbarbuttons-silk.png

	@php ./build/compress.php ${EDITOR_UNCOMPRESSED}.js ${EDITOR}.js;
	@cp ${EDITOR_UNCOMPRESSED}.css ${EDITOR}.css