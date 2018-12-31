### Description
Generates Unique IDentifiers used to identify information in computer systems.  
For UUID version 3, 4 and 5 is VALID RFC 4122 COMPLIANT.

### Detail module information

1. Namespace >> **modules/genuid**
2. Zip Archive source >> 
    https://github.com/aalfiann/reSlim-modules-genuid/archive/master.zip

### How to Integrate this module into reSlim?

1. Download zip then upload to reSlim server to the **modules/**
2. Extract zip then you will get new folder like **reSlim-modules-genuid-master**
3. Rename foldername **reSlim-modules-genuid-master** to **genuid**
4. Done

### How to Integrate this module into reSlim with Packager?

1. Make AJAX GET request to >>
    http://**{yourdomain.com}**/api/packager/install/zip/safely/**{yourusername}**/**{yourtoken}**/?lang=en&source=**{zip archive source}**&namespace=**{modul namespace}**