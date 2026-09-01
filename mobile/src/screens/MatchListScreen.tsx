import React, { useEffect, useState } from 'react';
import {
  View, Text, ScrollView, TouchableOpacity,
  StyleSheet, ActivityIndicator, Alert, RefreshControl
} from 'react-native';
import { getMatches, startMatch, halfTime, resumeMatch, finishMatch } from '../services/api';
import { subscribeToMatch } from '../services/pusher';

interface Match {
  id: number;
  status: string;
  minute: number | null;
  home_score: number;
  away_score: number;
  scheduled_at: string;
  home_team: { name: string; short_name: string; primary_color: string };
  away_team: { name: string; short_name: string; primary_color: string };
}

export default function MatchListScreen({ onSelectMatch }: { onSelectMatch: (m: Match) => void }) {
  const [matches,   setMatches]   = useState<Match[]>([]);
  const [loading,   setLoading]   = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const load = async (silent = false) => {
    if (!silent) setLoading(true);
    try {
      const { data } = await getMatches();
      setMatches(data);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { load(); }, []);

  const statusLabel: Record<string, string> = {
    scheduled: 'Programmé', live: '🔴 En cours', half_time: '⏸ Mi-temps',
    finished: '✅ Terminé', postponed: 'Reporté', cancelled: 'Annulé',
  };

  if (loading) return <ActivityIndicator style={{ flex: 1 }} color="#6366f1" />;

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(true); }} />}
    >
      {matches.map(m => (
        <TouchableOpacity key={m.id} style={styles.card} onPress={() => onSelectMatch(m)}>
          <Text style={styles.status}>{statusLabel[m.status] ?? m.status}</Text>
          <View style={styles.row}>
            <Text style={styles.team}>{m.home_team.short_name}</Text>
            <Text style={styles.score}>{m.home_score} – {m.away_score}</Text>
            <Text style={styles.team}>{m.away_team.short_name}</Text>
          </View>
          {m.status === 'live' && <Text style={styles.minute}>{m.minute}'</Text>}
        </TouchableOpacity>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0f172a', padding: 16 },
  card:      { backgroundColor: '#1e293b', borderRadius: 12, padding: 16, marginBottom: 12 },
  status:    { color: '#94a3b8', fontSize: 12, marginBottom: 8 },
  row:       { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  team:      { color: '#fff', fontSize: 20, fontWeight: 'bold', flex: 1, textAlign: 'center' },
  score:     { color: '#6366f1', fontSize: 28, fontWeight: 'bold', width: 80, textAlign: 'center' },
  minute:    { color: '#f59e0b', fontSize: 12, textAlign: 'center', marginTop: 6 },
});
