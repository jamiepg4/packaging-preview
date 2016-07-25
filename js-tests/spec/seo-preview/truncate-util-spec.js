var truncateString = require('seo-utils/truncate-util');

describe('When truncating string', function() {
    it('should not truncate given the text is in limit', function() {
        var notTruncatedString = truncateString('abcdefghij', 26);
        expect(notTruncatedString).toBe('abcdefghij');
    });

    it('should truncate given the text is over the limit', function() {
        var notTruncatedString = truncateString('abcdefghij', 5);
        expect(notTruncatedString).toBe('abcde...');
    });

    it('should truncate given the text with words is over the limit', function() {
        var notTruncatedString = truncateString('ab asdf sadf', 5);
        expect(notTruncatedString).toBe('ab...');
    });
});
