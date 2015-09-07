(function($) {
  $(document).ready(function(doc){
// do after load document
});
})(window.jQuery);

ben ra conflict

dan untuk implementasi klik gaweo 
$(document).on('click', 'target', function(e){
e.preventDefault();
// lek ada propagansi misal file upload 

e.stopPropagation();
});
