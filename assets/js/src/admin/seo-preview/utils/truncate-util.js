var TruncateString = function( string, limit, rightPad ) {
	var breakPoint, substr;
	var breakChar = ' ';

	if ( ! rightPad ) {
		rightPad = '...';
	}

	if ( ! string || string.length <= limit ) {
		return string;
	}

	substr = string.substr(0, limit);
	if ( ( breakPoint = substr.lastIndexOf( breakChar )  ) >= 0 ) {
		if ( breakPoint < string.length -1 ) {
			return string.substr( 0, breakPoint ) + rightPad;
		}
	} else {
		return substr + rightPad;
	}
	return string;
};

module.exports = TruncateString;
