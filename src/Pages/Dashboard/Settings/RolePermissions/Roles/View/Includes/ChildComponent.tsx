import { useState } from 'react';

type ChildComponentType = {
  onChildCheckboxChange: (checked: boolean) => void
}
const ChildComponent = ({ onChildCheckboxChange }: ChildComponentType) => {
  const [isChecked, setIsChecked] = useState(false);

  const handleChildCheckboxChange = (checked: boolean) => {
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
