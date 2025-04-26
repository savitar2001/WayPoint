import React from 'react';
import PropTypes from 'prop-types';
import './List.css';

const List = ({ items, renderItem, onItemClick, emptyMessage }) => {
  return (
    <div className="list-module">
      {items.length > 0 ? (
        items.map((item, index) => (
          <div
            key={item.id || index}
            className="list-item"
            onClick={() => onItemClick && onItemClick(item)}
          >
            {renderItem(item)}
          </div>
        ))
      ) : (
        <div className="empty-message">{emptyMessage}</div>
      )}
    </div>
  );
};

List.propTypes = {
  items: PropTypes.array.isRequired,
  renderItem: PropTypes.func.isRequired,
  onItemClick: PropTypes.func,
  emptyMessage: PropTypes.string,
};

export default List;