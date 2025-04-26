import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './FileUploader.css';

const FileUploader = ({ onFileUpload }) => {
  const [files, setFiles] = useState([]);
  const [previews, setPreviews] = useState([]); // 新增預覽狀態

  
  const handleFileChange = (e) => {
    const selectedFiles = Array.from(e.target.files);
    setFiles(selectedFiles);

    // 生成預覽 URL
    const previewUrls = selectedFiles.map((file) =>
      URL.createObjectURL(file)
    );
    setPreviews(previewUrls);

    if (onFileUpload) {
      onFileUpload(selectedFiles); // 調用回調函數
    }
  };

  const handleRemovePreview = (index) => {
    // 移除指定的預覽
    const updatedPreviews = previews.filter((_, i) => i !== index);
    setPreviews(updatedPreviews);

    const updatedFiles = files.filter((_, i) => i !== index);
    setFiles(updatedFiles);
  };

  return (
    <div className="file-uploader-container">
      <label className="file-uploader-label">
        <input
          type="file"
          multiple={false} // 限制為單文件上傳
          accept="image/*"
          onChange={handleFileChange}
          className="file-uploader-input"
        />
        <span>Upload Image</span>
      </label>
       {/* 預覽區域 */}
       <div className="file-preview-container">
        {previews.map((preview, index) => (
          <div className="file-preview" key={index}>
            <img
              src={preview}
              alt={`Preview ${index}`}
              className="file-preview-image"
            />
            <button
              className="file-remove-button"
              onClick={() => handleRemovePreview(index)}
            >
              &times;
            </button>
          </div>
        ))}
      </div>
    </div>
  );
};

FileUploader.propTypes = {
  onFileUpload: PropTypes.func.isRequired, // 文件上傳回調函數
};

export default FileUploader;