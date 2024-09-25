jQuery(document).ready(function() {
    jQuery('td.author.column-author').each(function() {
        var commentDate = jQuery(this).find('p.commentaire-date');
        commentDate.appendTo(jQuery(this).find('strong'));
    });
});