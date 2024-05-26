import React, { createContext, useContext, useState } from 'react';
import CryptoJS from 'crypto-js';
import { config } from '@/utils/helpers';
import { AuthenticatedUser, UserInterface } from '@/interfaces/AuthInterfaces';

// Secret key for encryption/decryption
const secretKey = import.meta.env.VITE_APP_CRYPO_SECRET_KEY;

// Function to decrypt the user object
const decryptUser = (encryptedUser: string) => {
  try {
    if (!encryptedUser) {
      return null;
    }

    const bytes = CryptoJS.AES.decrypt(encryptedUser, secretKey);
    const decryptedData = bytes.toString(CryptoJS.enc.Utf8);

    if (!decryptedData) {
      return null;
    }

    const decryptedUser = JSON.parse(decryptedData) as UserInterface;
    return decryptedUser;
  } catch (error) {
    return null;
  }
};

// Create the AuthContent context with the generic interface
const AuthContent = createContext<AuthenticatedUser>({
  user: null,
  updateUser: () => { },
  csrfToken: async () => false,
  setUser: () => { },
  deleteUser: () => { },
  verified: false,
  setVerified: () => { },
  redirectTo: config.urls.home,
  setRedirectTo: () => { },
  redirectMessage: undefined,
  setRedirectMessage: () => { },
  fileAccessToken: null

});

// Function to encrypt the user object
const encryptData = (user: UserInterface) => {
  try {
    return CryptoJS.AES.encrypt(JSON.stringify(user), secretKey).toString();
  } catch (error) {
    return null;
  }
};

// Authentication Provider component that wraps the application with authentication capabilities
export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
  // Get the stored user data from localStorage
  
  // Initialize the 'user' state with the decrypted user data (if available) or null
  const [user, _setUser] = useState<UserInterface | null>(() => {

    const storedUser = localStorage.getItem(`${config.storageName}.user`);
    
    try {
      if (storedUser) {
        const decryptedUser = decryptUser(storedUser);
        return decryptedUser;
      }
      return null;
    } catch (error) {
      return null;
    }
  });

  const [verified, setVerified] = useState<boolean>(false)
  const [redirectTo, setRedirectTo] = useState<string>(config.urls.home)
  const [redirectMessage, setRedirectMessage] = useState<string>()

  // Set encrypted user data to local storage and update roles state
  const setUser = (newUser: UserInterface) => {

    if (newUser && !newUser.id) return null

    if (newUser) {
      const encryptedUser = encryptData(newUser);
      localStorage.setItem(`${config.storageName}.user`, encryptedUser);
    } else {
      localStorage.removeItem(`${config.storageName}.user`);
    }
    _setUser(newUser);
  };

  // Function to update the user object and store it in localStorage
  const updateUser = (updatedUser: Partial<UserInterface>) => {
    if (user) {
      // Merge the updatedUser into the existing user object (excluding the token key)
      const updatedUserData = { ...user, ...updatedUser, token: user.token };

      // Encrypt the updated user object and store it in localStorage
      const encryptedUser = encryptData(updatedUserData);
      if (encryptedUser) {
        localStorage.setItem(`${config.storageName}.user`, encryptedUser);
        setVerified(true)
        setFileAccessToken(updatedUserData.fileAccessToken); // Set the file access token


      }

      // Update the 'user' state with the updated user object
      setUser(updatedUserData);
    }
  };

  // Function to generate CSRF token for guest methods (You may implement actual CSRF token generation logic here)
  const csrfToken = async () => {
    return false;
  };

  // Function to delete user data from localStorage and set the user to null
  const deleteUser = () => {
    localStorage.removeItem(`${config.storageName}.user`);
    _setUser(null);
  };

  const [fileAccessToken, setFileAccessToken] = useState<string | null>(null);

  // Provide the authentication data and functions to the children components
  return (
    <AuthContent.Provider value={{ user, updateUser, csrfToken, setUser, deleteUser, verified, setVerified, redirectTo, setRedirectTo, setRedirectMessage, redirectMessage, fileAccessToken }}>
      {children}
    </AuthContent.Provider>
  );
};

// Custom hook to access the authentication context
export const useAuth = () => {
  return useContext(AuthContent);
};

