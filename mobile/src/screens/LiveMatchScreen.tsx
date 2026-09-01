import React, { useEffect, useRef, useState } from 'react';
import {
  View, Text, ScrollView, TouchableOpacity,
  StyleSheet, Alert, TextInput, Modal
} from 'react-native';
import { getMatch, startMatch, halfTime, resumeMatch, finishMatch, addEvent, updateMinute } from '../services/api';
import { subscribeToMatch } from '../services/pusher';

const EVENT_TYPES = [
  { key: 'goal',             label: '⚽ But' },
  { key: 'own_goal',         label: '🔴 CSC' },
  { key: 'yellow_card',      label: '🟡 Jaune' },
  { key: 'red_card',         label: '🔴 Rouge' },
  { key: 'yellow_red_card',  label: '🟡🔴 2ème jaune' },
  { key: 'substitution_in',  label: '🔄 Entrée' },
  { key: 'substitution_out', label: '🔄 Sortie' },
  { key: 'penalty_scored',   label: '⚽ Penalty marqué' },
  { key: 'penalty_missed',   label: '❌ Penalty raté' },
];

export default function LiveMatchScreen({ matchId }: { matchId: number }) {
  const [match, setMatch]        = useState<any>(null);
  const [loading, setLoading]    = useState(true);
  const [showEvent, setShowEvent] = useState(false);
  const [eventType, setEventType] = useState('goal');
  const [minute, setMinute]      = useState('');
  const [teamId, setTeamId]      = useState<number | null>(null);
  const unsubRef = useRef<(() => void) | null>(null);

  const load = async () => {
    const { data } = await getMatch(matchId);
    setMatch(data);
    setLoading(false);
  };

  useEffect(() => {
    load();
    unsubRef.current = subscribeToMatch(matchId, {
      onStatus:  (d) => setMatch((m: any) => m ? { ...m, status: d.status, minute: d.minute } : m),
      onScore:   (d) => setMatch((m: any) => m ? { ...m, home_score: d.home_score, away_score: d.away_score } : m),
      onMinute:  (d) => setMatch((m: any) => m ? { ...m, minute: d.minute } : m),
      onEvent:   () => load(),
    });
    return () => unsubRef.current?.();
  }, [matchId]);

  if (loading || !match) return null;

  const confirmAction = (label: string, action: () => Promise<any>) =>
    Alert.alert('Confirmation', `${label} ?`, [
      { text: 'Annuler', style: 'cancel' },
      { text: 'Confirmer', onPress: async () => { try { await action(); await load(); } catch (e: any) { Alert.alert('Erreur', e?.response?.data?.message ?? 'Erreur réseau'); } } },
    ]);

  const submitEvent = async () => {
    if (!minute || !teamId) {
      Alert.alert('Erreur', 'Minute et équipe obligatoires');
      return;
    }
    try {
      await addEvent(matchId, { type: eventType, minute: parseInt(minute, 10), team_id: teamId });
      setShowEvent(false);
      setMinute('');
      await load();
    } catch (e: any) {
      Alert.alert('Erreur', e?.response?.data?.message ?? 'Erreur réseau');
    }
  };

  return (
    <ScrollView style={styles.container}>
      {/* Score */}
      <View style={styles.scoreBox}>
        <Text style={styles.teamName}>{match.home_team.short_name}</Text>
        <View>
          <Text style={styles.score}>{match.home_score} – {match.away_score}</Text>
          {match.minute && <Text style={styles.minute}>{match.minute}'</Text>}
          <Text style={styles.statusLabel}>{match.status.toUpperCase()}</Text>
        </View>
        <Text style={styles.teamName}>{match.away_team.short_name}</Text>
      </View>

      {/* Contrôles */}
      <View style={styles.ctrlRow}>
        {match.status === 'scheduled' && (
          <TouchableOpacity style={styles.btnGreen}  onPress={() => confirmAction('Démarrer le match', () => startMatch(matchId))}>
            <Text style={styles.btnText}>▶ Démarrer</Text>
          </TouchableOpacity>
        )}
        {match.status === 'live' && (
          <>
            <TouchableOpacity style={styles.btnYellow} onPress={() => confirmAction('Siffler la mi-temps', () => halfTime(matchId))}>
              <Text style={styles.btnText}>⏸ Mi-temps</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.btnGreen} onPress={() => setShowEvent(true)}>
              <Text style={styles.btnText}>＋ Événement</Text>
            </TouchableOpacity>
          </>
        )}
        {match.status === 'half_time' && (
          <TouchableOpacity style={styles.btnGreen} onPress={() => confirmAction('Reprendre le match', () => resumeMatch(matchId))}>
            <Text style={styles.btnText}>▶ Reprendre</Text>
          </TouchableOpacity>
        )}
        {['live', 'half_time'].includes(match.status) && (
          <TouchableOpacity style={styles.btnRed} onPress={() => confirmAction('Terminer le match', () => finishMatch(matchId))}>
            <Text style={styles.btnText}>■ Terminer</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Événements */}
      <Text style={styles.sectionTitle}>Événements</Text>
      {match.events?.map((ev: any) => (
        <View key={ev.id} style={styles.eventRow}>
          <Text style={styles.eventMinute}>{ev.minute}'</Text>
          <Text style={styles.eventType}>{ev.type.replace('_', ' ')}</Text>
          <Text style={styles.eventPlayer}>{ev.player?.last_name ?? '—'}</Text>
        </View>
      ))}

      {/* Modal saisie événement */}
      <Modal visible={showEvent} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalBox}>
            <Text style={styles.modalTitle}>Nouvel événement</Text>

            <Text style={styles.label}>Type</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false}>
              {EVENT_TYPES.map(et => (
                <TouchableOpacity key={et.key} style={[styles.chip, eventType === et.key && styles.chipActive]}
                  onPress={() => setEventType(et.key)}>
                  <Text style={styles.chipText}>{et.label}</Text>
                </TouchableOpacity>
              ))}
            </ScrollView>

            <Text style={styles.label}>Minute</Text>
            <TextInput style={styles.input} keyboardType="number-pad" value={minute} onChangeText={setMinute} placeholder="ex: 45" placeholderTextColor="#666" />

            <Text style={styles.label}>Équipe</Text>
            <View style={styles.teamRow}>
              {[match.home_team, match.away_team].map((t: any) => (
                <TouchableOpacity key={t.id} style={[styles.chip, teamId === t.id && styles.chipActive]}
                  onPress={() => setTeamId(t.id)}>
                  <Text style={styles.chipText}>{t.short_name}</Text>
                </TouchableOpacity>
              ))}
            </View>

            <View style={styles.modalActions}>
              <TouchableOpacity style={styles.btnRed}    onPress={() => setShowEvent(false)}><Text style={styles.btnText}>Annuler</Text></TouchableOpacity>
              <TouchableOpacity style={styles.btnGreen}  onPress={submitEvent}><Text style={styles.btnText}>Valider</Text></TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container:    { flex: 1, backgroundColor: '#0f172a' },
  scoreBox:     { flexDirection: 'row', justifyContent: 'space-around', alignItems: 'center', backgroundColor: '#1e293b', margin: 16, borderRadius: 16, padding: 24 },
  teamName:     { color: '#fff', fontSize: 22, fontWeight: 'bold', textAlign: 'center', flex: 1 },
  score:        { color: '#6366f1', fontSize: 40, fontWeight: 'bold', textAlign: 'center' },
  minute:       { color: '#f59e0b', textAlign: 'center', fontSize: 14 },
  statusLabel:  { color: '#64748b', textAlign: 'center', fontSize: 11, marginTop: 4 },
  ctrlRow:      { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginHorizontal: 16, marginBottom: 16 },
  btnGreen:     { flex: 1, backgroundColor: '#16a34a', borderRadius: 10, padding: 14, alignItems: 'center' },
  btnYellow:    { flex: 1, backgroundColor: '#ca8a04', borderRadius: 10, padding: 14, alignItems: 'center' },
  btnRed:       { flex: 1, backgroundColor: '#dc2626', borderRadius: 10, padding: 14, alignItems: 'center' },
  btnText:      { color: '#fff', fontWeight: 'bold' },
  sectionTitle: { color: '#94a3b8', fontSize: 13, marginHorizontal: 16, marginBottom: 8, textTransform: 'uppercase' },
  eventRow:     { flexDirection: 'row', paddingHorizontal: 16, paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#1e293b' },
  eventMinute:  { color: '#f59e0b', width: 36 },
  eventType:    { color: '#94a3b8', flex: 1 },
  eventPlayer:  { color: '#fff', flex: 1, textAlign: 'right' },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.7)', justifyContent: 'flex-end' },
  modalBox:     { backgroundColor: '#1e293b', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 24 },
  modalTitle:   { color: '#fff', fontSize: 18, fontWeight: 'bold', marginBottom: 16 },
  label:        { color: '#94a3b8', fontSize: 12, marginBottom: 8, marginTop: 12 },
  input:        { backgroundColor: '#0f172a', color: '#fff', borderRadius: 8, padding: 12, borderWidth: 1, borderColor: '#334155' },
  chip:         { backgroundColor: '#334155', borderRadius: 20, paddingHorizontal: 12, paddingVertical: 6, marginRight: 8 },
  chipActive:   { backgroundColor: '#6366f1' },
  chipText:     { color: '#fff', fontSize: 12 },
  teamRow:      { flexDirection: 'row', gap: 12 },
  modalActions: { flexDirection: 'row', gap: 12, marginTop: 20 },
});
