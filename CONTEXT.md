# WP Thumbhash

Generates compact blurry placeholders for WordPress images, so pages can show something meaningful while the real image loads.

## Language

**Image**:
A WordPress attachment of an image type.
_Avoid_: Attachment, media, file

**Encodable image**:
An image a thumbhash can be generated for: any raster image, i.e. not SVG.
_Avoid_: Supported image, valid image

**Thumbhash**:
The short encoded string representing an image's colors and rough shape, stored with the image.
_Avoid_: Hash, blurhash, placeholder (when meaning the string)

**Placeholder**:
The visual stand-in rendered from a thumbhash in the frontend, shown until the image loads.
_Avoid_: Preview, thumbnail, blur

**Render strategy**:
How a placeholder is displayed: `canvas` (default), `img` (data URI) or `average` (the image's average color only).
_Avoid_: Mode, type

**Generate**:
Encode an image into a thumbhash and store it. Happens on upload, via WP-CLI or from the admin.
_Avoid_: Create, compute, encode (except for the raw encoding step)

**Clear**:
Remove stored thumbhashes from images.
_Avoid_: Delete, reset
