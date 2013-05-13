bionames-archive
================

Local rchive of PDFs. Converts to images and text for viewing in DocumentCloud.

### Storage

PDFs themselves are stored locally in a directory using the sha1 of the PDF as the file name. You will
need a locally writable directory for the PDFs, and make a symbolic link to a folder called "pdf".

	ln -s /Volumes/LaCie/archive/pdf/

Make sure pdf folder is writable by the web server

### Prerequisites


#### libpng

http://libpng.sourceforge.net/

#### libjpeg

http://libjpeg.sourceforge.net

Remember to 
	sudo make install-lib

If building on a Mac and you get an error like

	./configure: /bin/sh^M: bad interpreter: No such file or directory
	
it's probably due to the configure file have Windows end-of-line characters. Convert to Unix and it should work

#### libTIFF

http://www.libtiff.org

#### GhostScript

http://www.ghostscript.com/download/

#### Free Type

http://www.freetype.org

#### ImageMagick

(after installing dependencies above)

#### OptiPNG

Optimise size of PNGs

http://optipng.sourceforge.net

#### Xpdf

http://www.foolabs.com/xpdf/

Tell it where freetype is

	./configure --with-freetype2-includes=/usr/local/include/freetype2/


#### DjView4

http://djvu.sourceforge.net/djview4.html

If you install DjView into /Applications then the DjVu binaries will be in the folder 

	/Applications/DjView.app/Contents/bin



