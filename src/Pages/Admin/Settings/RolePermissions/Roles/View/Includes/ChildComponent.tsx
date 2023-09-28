import React, { useState } from 'react';

const ChildComponent = ({ parentChecked, onChildCheckboxChange }) => {
  const [isChecked, setIsChecked] = useState(false);

  const handleChildCheckboxChange = (checked) => {
    setIsChecked(checked);
    onChildCheckboxChange(checked);
  };

  return (
    <div>
      <input
        type="checkbox"
        checked={isChecked}
        onChange={(e) => handleChildCheckboxChange(e.target.checked)}
      />
      <span>Child Checkbox</span>
    </div>
  );
};

export default ChildComponent;
