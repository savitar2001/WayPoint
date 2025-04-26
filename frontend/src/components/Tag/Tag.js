import React, { useState } from 'react';
import './Tag.css';

const Tag = ({ onTagsChange }) => {
  const [tags, setTags] = useState([]);
  const [inputValue, setInputValue] = useState('');

  const handleAddTag = () => {
    if (inputValue.trim() && !tags.includes(inputValue)) {
      const updatedTags = [...tags, inputValue];
      setTags(updatedTags);
      onTagsChange(updatedTags); // 回調傳遞標籤數據
      setInputValue('');
    }
  };

  const handleRemoveTag = (tagToRemove) => {
    const updatedTags = tags.filter(tag => tag !== tagToRemove);
    setTags(updatedTags);
    onTagsChange(updatedTags); // 回調傳遞標籤數據
  };

  return (
    <div className="tag-container">
      <div className="tag-input">
        <input
          type="text"
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value)}
          placeholder="Add a tag"
        />
        <button onClick={handleAddTag}>Add</button>
      </div>
      <div className="tag-list">
        {tags.map((tag, index) => (
          <div key={index} className="tag-item">
            {tag}
            <button className="remove-tag" onClick={() => handleRemoveTag(tag)}>x</button>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Tag;