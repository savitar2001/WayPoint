import React, { useState } from 'react';
import './TextArea.css';

const Textarea = ({ placeholder = 'Write your post here...', maxLength = 200, onChange, value }) => {
  return (
    <div className="textarea-container">
      <textarea
        className="textarea-input"
        placeholder={placeholder}
        value={value}
        onChange={onChange}
        maxLength={maxLength}
      />
      <div className="textarea-footer">
        <span className="char-count">
          {value.length}/{maxLength}
        </span>
      </div>
    </div>
  );
};
export default Textarea;