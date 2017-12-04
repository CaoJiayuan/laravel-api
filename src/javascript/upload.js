const axios = require('axios');

/**
 *
 * @param {File} file
 * @constructor
 */
function UploadFile(file) {
    this.file = file;
}

UploadFile.KB = 1024;
UploadFile.MB = 1024 * 1024;

UploadFile.prototype.isImage = function () {
    return this.file.type.indexOf('image') >= 0;
};

UploadFile.prototype.isVideo = function () {
    return this.file.type.indexOf('video') >= 0;
};

UploadFile.prototype.getSize = function () {
    return this.file.size;
};

UploadFile.prototype.notExceeding = function (size) {
    return this.getSize() <= size;
};

UploadFile.prototype.invalidFileMessage = '文件格式不正确';

const defaultConfig = {
    progress : () => {
    },
    validate : uploadFile => true,
    chunk    : false,
    success  : data => {
    },
    error    : data => {

    },
    chunkSize: UploadFile.MB
};


fileUpload = function (el, url, config) {
    config = config || {};
    let name = el.name || 'file', id = el.id;
    let file = el.files[0];
    let configs = Object.assign(defaultConfig, config);
    let uploadFile = new UploadFile(file);
    let validate = configs.validate;

    if (validate(uploadFile)) {
        let success = configs.success;
        let error = configs.error;
        let progress = configs.progress;
        if (configs.chunk) {
            return chunkUpload(file, name, url, success, error, progress, configs.chunkSize);
        }

        let fd = new FormData();
        fd.append(name, file);
        let config = {
            onUploadProgress: progressEvent => {
                let percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progress(percentCompleted, progressEvent)
            }
        };
        axios.post(url, fd, config).then(response => {
            success(response.data, response)
        }).catch(error => {
            return Promise.reject(error.response);
        }).catch(response => {
            error(response.data, response);
        })
    }

};

let uploading = true;

/**
 *
 * @param {File} file
 * @param name
 * @param url
 * @param success
 * @param error
 * @param progress
 * @param chunkSize
 */
chunkUpload = function (file, name, url, success, error, progress, chunkSize) {
    chunkSize = chunkSize || UploadFile.MB;
    let total = file.size;
    chunkSize = chunkSize > total ? total : chunkSize;
    let chunks = [], start = 0;
    let fileId = md5(file.name + new Date().getTime());
    let index = 1;
    while (total > 0) {
        if (total < chunkSize) {
            chunkSize = total;
        }
        total -= chunkSize;
        let end = start + chunkSize;
        let blob = file.slice(start, end);
        chunks.push({
            blob      : blob,
            start     : start,
            end       : end,
            total     : file.size,
            filename  : file.name,
            mime_type : file.type,
            file_id   : fileId,
            index     : index,
            chunk_size: chunkSize
        });
        index++;
        start = end;
    }
    let count = chunks.length;
    let i = 0;
    chunks.forEach(chunk => {
        uploadChunk(name, url, chunk, count, (data, response) => {
            i++;
            progress(Math.round((i * 100) / count));
            if (response.status === 200) {
                success(data, response);
            }

        }, (data, response) => {
            error(data, response);
        });
    });
};

uploadChunk = function (name, url, {blob, start, end, total, chunk_size, filename, file_id, index, mime_type}, count, onSuccess, onError) {
    onSuccess = onSuccess || function () {
    };
    onError = onError || function () {
    };
    let fd = new FormData();
    fd.append(name, blob);
    fd.append('chunk_start', start);
    fd.append('filename', filename);
    fd.append('chunk_end', end);
    fd.append('total_length', total);
    fd.append('chunk_size', chunk_size);
    fd.append('mime_type', mime_type);
    fd.append('file_id', file_id);
    fd.append('chunks', count);
    fd.append('chunk_index', index);
    setTimeout(() => {
        if (!uploading) {
            return;
        }
        axios.post(url, fd, {
            headers: {
                'X-Uploaded-With': 'ChunkUpload'
            }
        }).then(response => {
            onSuccess(response.data, response)
        }).catch(error => {
            uploading = false;
            return Promise.reject(error.response);
        }).catch(response => {
            onError(response.data, response);
        });
    }, index * 100)
};

module.exports = {
    fileUpload,
    UploadFile
};