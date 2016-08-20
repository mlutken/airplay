<?php


/**
If the artist name is written 'Surname, FirstName'. Like eg. (Turner, Tina), 
we try reversing the name to 'Tina Turner'
\todo ML:TODO: Describe/find out which types of artist names where we need the '/' split stuff this 
function also does. I suspect it is for classical artist we might need this.
\sa DbInserter_artist_data
*/
function reverseArtistNameWithComma( $sArtistNameOrig )
{
    $sArtistNameReversed = $sArtistNameOrig;
    $a = explode( ',', $sArtistNameOrig );
    if ( count($a) == 2 ) {
        $first  = trim ( $a[1] );
        $last   = trim ( $a[0] );
        $a = explode( '/', $first );
        if ( count($a) == 1 ) {
            $sArtistNameReversed = $first . " " . $last;    
        }
        else {
            $sArtistNameReversed = trim( $a[0] ) . " " . $last;
            for ( $i = 1 ; $i < count($a); $i++ ) {
                $sArtistNameReversed .= " / " . trim( $a[$i] );
            }
        }
    }
    return $sArtistNameReversed; 
}



/**
If the artist name is written 'Surname FirstName'. Like eg. (Turner Tina), 
we try reversing the name to 'Tina Turner'. This function reverses a name that 
consists of excactly 2 words, whereas tha reverseArtistNameWithComma only does so
if the name has excactly two words seperated by a comma. 
\note So far the only site we have seen writin e.g. 'Michael Jackson' as 'Jackson Michael' 
without a comma is CDON(DK). All other use the more normal reversed form 'Jackson, Michael' - 
i.e. with a comma.
So to try this reverse should be a last attemt at finding the artist before actually just creating 
him as a new when inserting data.
\sa DbInserter_artist_data
*/
function reverseArtistName( $sArtistNameOrig )
{
    $sArtistNameReversed = $sArtistNameOrig;
    $a = explode( ' ', $sArtistNameOrig );
    if ( count($a) == 2 ) {
        $first  = trim ( $a[1] );
        $last   = trim ( $a[0] );
        $a = explode( '/', $first );
        $sArtistNameReversed = $first . " " . $last;    
    }
    return $sArtistNameReversed; 
}

?>
