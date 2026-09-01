import React, { useState } from 'react';
import {
  View, Text, TextInput, TouchableOpacity,
  StyleSheet, ActivityIndicator, Alert, KeyboardAvoidingView, Platform
} from 'react-native';
import { login } from '../services/api';

export default function LoginScreen({ onLogin }: { onLogin: () => void }) {
  const [email,    setEmail]    = useState('');
  const [password, setPassword] = useState('');
  const [loading,  setLoading]  = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Erreur', 'Renseignez l'email et le mot de passe.');
      return;
    }
    setLoading(true);
    try {
      await login(email.trim(), password);
      onLogin();
    } catch (err: any) {
      Alert.alert('Connexion refusée', err?.response?.data?.message ?? 'Identifiants incorrects.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <Text style={styles.title}>GFC</Text>
      <Text style={styles.subtitle}>Backoffice Terrain</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        placeholderTextColor="#999"
        autoCapitalize="none"
        keyboardType="email-address"
        value={email}
        onChangeText={setEmail}
      />
      <TextInput
        style={styles.input}
        placeholder="Mot de passe"
        placeholderTextColor="#999"
        secureTextEntry
        value={password}
        onChangeText={setPassword}
      />
      <TouchableOpacity style={styles.btn} onPress={handleLogin} disabled={loading}>
        {loading
          ? <ActivityIndicator color="#fff" />
          : <Text style={styles.btnText}>Se connecter</Text>
        }
      </TouchableOpacity>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container:  { flex: 1, justifyContent: 'center', padding: 24, backgroundColor: '#0f172a' },
  title:      { fontSize: 48, fontWeight: 'bold', color: '#fff', textAlign: 'center', marginBottom: 4 },
  subtitle:   { fontSize: 16, color: '#94a3b8', textAlign: 'center', marginBottom: 40 },
  input:      { backgroundColor: '#1e293b', color: '#fff', borderRadius: 10, padding: 14,
                fontSize: 16, marginBottom: 16, borderWidth: 1, borderColor: '#334155' },
  btn:        { backgroundColor: '#6366f1', borderRadius: 10, padding: 16, alignItems: 'center' },
  btnText:    { color: '#fff', fontWeight: 'bold', fontSize: 16 },
});
