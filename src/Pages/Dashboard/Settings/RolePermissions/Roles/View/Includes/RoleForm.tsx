import React, { useState } from 'react';
import useAxios from 'path/to/useAxios'; // Replace 'path/to/useAxios' with the actual path to your useAxios file

const RoleForm = () => {
  const [name, setName] = useState('');
  const { data, error, loading, fetchData } = useAxios('/roles', 'post', false); // Adjust the API endpoint URL accordingly
  // Note: In the above line, we pass 'false' as the third parameter to prevent auto-fetching on mount

  const handleSubmit = async (event) => {
    event.preventDefault();
    try {
      // Create the role
      const roleData = { name }; // Adjust the payload based on your backend API requirements
      await fetchData(roleData);
      console.log('Role created:', data); // 'data' will contain the response from the API
      // Reset the form
      setName('');
    } catch (error) {
      console.error('Error creating role:', error);
    }
  };

  return (
    <div>
      <h2>Create Role</h2>
      <form onSubmit={handleSubmit}>
        <label>
          Role Name:
          <input
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
          />
        </label>
        <button type="submit" disabled={loading}>
          Create Role
        </button>
        {error && <p>Error: {error}</p>}
      </form>
    </div>
  );
};

export default RoleForm;
