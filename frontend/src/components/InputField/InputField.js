import React from 'react';
import Button from '../Button/Button';
import './InputField.css';

const InputField = ({ formName, fields, onButtonClick  }) => {
  return (
    <div className="input-field-container">
      {/* 表單名稱 */}
      <h2 className="form-name">{formName}</h2>
 
      {/* 動態欄位 */}
      <form className="input-form">
        {fields.map((field, index) => (
          <div key={index} className="form-group">
            <label htmlFor={`field-${index}`} className="form-label">
              {field.label}
            </label>
            <input
              type={field.type || "text"}
              id={`field-${index}`}
              className="form-input"
              placeholder={field.placeholder}
              value={field.value || ''}
              onChange={field.onChange}
            />
          </div>
        ))}
        <Button
          type="button" 
          variant="primary"
          label="Submit"
          onClick={onButtonClick} 
        >Submit</Button>
      </form>
    </div>
  );
};

export default InputField;