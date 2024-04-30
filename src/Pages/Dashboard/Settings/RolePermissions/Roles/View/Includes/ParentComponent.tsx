import React, { useState } from 'react';
import ChildComponent from './ChildComponent';

const ParentComponent = () => {
  const [isChecked, setIsChecked] = useState(false);

  const handleParentCheckboxChange = (checked) => {
    setIsChecked(checked);
  };

  return (
    <div>
      <input
        type="checkbox"
        checked={isChecked}
        onChange={(e) => handleParentCheckboxChange(e.target.checked)}
      />
      <span>Parent Checkbox</span>
      <ChildComponent parentChecked={isChecked} onChildCheckboxChange={handleParentCheckboxChange} />
    </div>
  );
};

export default ParentComponent;
