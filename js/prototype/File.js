/**
 * https://stackoverflow.com/questions/36280818/how-to-convert-file-to-base64-in-javascript
 * 파일 오브젝트를 base64 인코딩 된거로 바꾸기
 */
File.prototype.base64 = function(){
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(this);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(this);
        reader.onload = () => {
            let encoded = reader.result.toString().replace(/^data:(.*,)?/, '');
            if ((encoded.length % 4) > 0) {
                encoded += '='.repeat(4 - (encoded.length % 4));
            }
            resolve(encoded);
        };
        reader.onerror = error => reject(error);
    });
}
