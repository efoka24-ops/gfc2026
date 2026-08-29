import React from 'react';
import { StatusBar } from 'expo-status-bar';
import { View, Text, Pressable, Image } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useFonts } from 'expo-font';
import { Anton_400Regular } from '@expo-google-fonts/anton';
import { Manrope_500Medium, Manrope_700Bold, Manrope_800ExtraBold } from '@expo-google-fonts/manrope';

import { colors, fonts } from './src/theme';
import Icon from './src/components/Icon';

import HomeScreen from './src/screens/HomeScreen';
import FixturesScreen from './src/screens/FixturesScreen';
import MatchScreen from './src/screens/MatchScreen';
import StandingsScreen from './src/screens/StandingsScreen';
import TeamsScreen from './src/screens/TeamsScreen';
import SquadScreen from './src/screens/SquadScreen';
import PlayerScreen from './src/screens/PlayerScreen';
import StatsScreen from './src/screens/StatsScreen';
import MediaScreen from './src/screens/MediaScreen';
import CompetitionsScreen from './src/screens/CompetitionsScreen';
import AboutScreen from './src/screens/AboutScreen';

import { FournisseurOperateur } from './src/auth';
import ConnexionScreen from './src/screens/operateur/ConnexionScreen';
import MesMatchsScreen from './src/screens/operateur/MesMatchsScreen';
import CompositionScreen from './src/screens/operateur/CompositionScreen';
import SaisieLiveScreen from './src/screens/operateur/SaisieLiveScreen';
import ClotureScreen from './src/screens/operateur/ClotureScreen';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

const header = ({ navigation, route, options, back }) => (
  <View style={{ backgroundColor: colors.bordeaux, paddingTop: 46, paddingBottom: 16, paddingHorizontal: 18, flexDirection: 'row', alignItems: 'center', gap: 12 }}>
    {back ? (
      <Pressable onPress={navigation.goBack} style={{ width: 34, height: 34, borderRadius: 10, backgroundColor: 'rgba(255,255,255,0.1)', alignItems: 'center', justifyContent: 'center' }}>
        <Icon name="back" size={18} color="#fff" strokeWidth={2.2} />
      </Pressable>
    ) : (
      <Image source={require('./assets/logo.png')} style={{ width: 34, height: 34 }} resizeMode="contain" />
    )}
    <View style={{ flex: 1 }}>
      <Text style={{ fontFamily: fonts.bodyBold, fontSize: 10, letterSpacing: 1.6, color: colors.orangeSoft, textTransform: 'uppercase' }}>
        {options.kicker ?? '6e édition'}
      </Text>
      <Text numberOfLines={1} style={{ fontFamily: fonts.display, fontSize: 21, letterSpacing: 0.4, color: '#fff', textTransform: 'uppercase', marginTop: 4 }}>
        {options.title ?? route.name}
      </Text>
    </View>
    <Pressable onPress={() => navigation.navigate('Apropos')} style={{ width: 34, height: 34, borderRadius: 10, backgroundColor: 'rgba(255,255,255,0.1)', alignItems: 'center', justifyContent: 'center' }}>
      <Icon name="bell" size={17} color="#fff" />
    </Pressable>
  </View>
);

const TABS = [
  { name: 'Accueil',    icon: 'home',     component: HomeScreen,      title: 'Garoua Football Challenge' },
  { name: 'Matchs',     icon: 'calendar', component: FixturesScreen,  title: 'Calendrier', kicker: 'Toutes compétitions' },
  { name: 'Classement', icon: 'trophy',   component: StandingsScreen, title: 'Classement', kicker: 'Championnat' },
  { name: 'Equipes',    icon: 'shield',   component: TeamsScreen,     title: 'Équipes', kicker: '10 équipes engagées' },
  { name: 'Stats',      icon: 'chart',    component: StatsScreen,     title: 'Statistiques', kicker: 'Championnat' },
];

function Tabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        header,
        tabBarActiveTintColor: colors.bordeaux,
        tabBarInactiveTintColor: '#B8A99E',
        tabBarStyle: { backgroundColor: '#fff', borderTopColor: colors.line, height: 64, paddingTop: 6, paddingBottom: 8 },
        tabBarLabelStyle: { fontFamily: fonts.bodyBold, fontSize: 9.5 },
      }}
    >
      {TABS.map((t) => (
        <Tab.Screen
          key={t.name}
          name={t.name}
          component={t.component}
          options={{
            title: t.title,
            kicker: t.kicker,
            tabBarLabel: t.name === 'Equipes' ? 'Équipes' : t.name,
            tabBarIcon: ({ color }) => <Icon name={t.icon} size={21} color={color} />,
          }}
        />
      ))}
    </Tab.Navigator>
  );
}

export default function App() {
  const [ready] = useFonts({ Anton_400Regular, Manrope_500Medium, Manrope_700Bold, Manrope_800ExtraBold });
  if (!ready) return <View style={{ flex: 1, backgroundColor: colors.bordeaux }} />;

  return (
    <FournisseurOperateur>
      <NavigationContainer>
        <StatusBar style="light" backgroundColor={colors.bordeaux} />
        <Stack.Navigator screenOptions={{ header, contentStyle: { backgroundColor: colors.cream } }}>
          {/* ----------------------------------- consultation publique */}
          <Stack.Screen name="Tabs" component={Tabs} options={{ headerShown: false }} />
          <Stack.Screen name="Match" component={MatchScreen} options={{ title: 'Match', kicker: 'En direct' }} />
          <Stack.Screen name="Effectif" component={SquadScreen} options={{ title: 'Effectif' }} />
          <Stack.Screen name="Joueur" component={PlayerScreen} options={{ title: 'Fiche joueur' }} />
          <Stack.Screen name="Medias" component={MediaScreen} options={{ title: 'Photos & vidéos', kicker: 'Édition en cours' }} />
          <Stack.Screen name="Competitions" component={CompetitionsScreen} options={{ title: 'Compétitions', kicker: '3 compétitions' }} />
          <Stack.Screen name="Apropos" component={AboutScreen} options={{ title: 'À propos', kicker: 'Depuis 2020' }} />

          {/* ------------------------------------ espace opérateur (US8)
              Cloisonné de la consultation : aucun onglet n'y mène, l'accès
              se fait par la page « À propos » et tout y exige un compte. */}
          <Stack.Screen name="Connexion" component={ConnexionScreen}
            options={{ title: 'Espace opérateur', kicker: 'Réservé à l\'organisation' }} />
          <Stack.Screen name="MesMatchs" component={MesMatchsScreen}
            options={{ title: 'Mes matchs', kicker: 'Espace opérateur' }} />
          <Stack.Screen name="Composition" component={CompositionScreen}
            options={{ title: 'Composition', kicker: 'Avant le match' }} />
          <Stack.Screen name="SaisieLive" component={SaisieLiveScreen}
            options={{ title: 'Saisie du match', kicker: 'Pendant le match' }} />
          <Stack.Screen name="Cloture" component={ClotureScreen}
            options={{ title: 'Après-match', kicker: 'Clôture' }} />
        </Stack.Navigator>
      </NavigationContainer>
    </FournisseurOperateur>
  );
}
