#Changelog#
v2.2.0  
- Seperated classes in Http\ namespace  
- Removed incomplete tests  
- Rewritten tests to be compatible with the latest structure  
- Added composer.json file, soon to be commited to packagist  

v2.1.1  
- Changed the way HTTP error codes are handled to a more specific way  
- Fixed a bug where HttpResponse::isSuccess(); would return false, when a redirect was recieved  
  
v2.1  
- Implemented classes autoloading functionality  
- Introduced HttpResponse::asXml(); methoded  
- Updated documentation  
  
v2.0 - **Backwards incompatible**  
- Renamed from WebRequest to a more generic HttpRequest
- Divided Request and Response logic into two seperate classes
- Added autoloader file
- Removed deprecated getCookies(); method