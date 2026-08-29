USE gfc;

INSERT INTO seasons (id,name,year,is_current) VALUES (1,'6e édition',2026,1);

INSERT INTO competitions (id,season_id,name,slug,type,description,start_date,end_date,sort_order) VALUES
(1,1,'Championnat GFC','championnat','league','Poule unique de 10 équipes en matchs aller simple, 9 journées. Le premier est champion et se qualifie pour la Super Coupe.','2026-07-04','2026-09-06',1),
(2,1,'Grand Prix Gabriel MBAÏROBÉ','grand-prix-mbairobe','cup','Épreuve reine de l''édition, à élimination directe : les 10 équipes, puis quarts, demi-finales et finale sur un seul match.','2026-07-11','2026-09-07',2),
(3,1,'Super Coupe','super-coupe','supercup','Affiche de clôture entre le champion et le vainqueur du Grand Prix Gabriel MBAÏROBÉ.','2026-09-12','2026-09-12',3);

-- À REMPLACER par les 10 véritables équipes de l'édition
INSERT INTO teams (id,name,abbr,quarter,founded_year,color) VALUES
(1,'AS Bénoué','BEN','Bénoué',2016,'#7A1F30'),
(2,'Roumdé Adjia FC','RAD','Roumdé Adjia',2015,'#B8452A'),
(3,'Foudre de Djamboutou','FDJ','Djamboutou',2018,'#8E3B2B'),
(4,'Étoile de Poumpoumré','EPO','Poumpoumré',2017,'#A2512E'),
(5,'Faro United','FAR','Faro',2019,'#6E2434'),
(6,'Lopéré FC','LOP','Lopéré',2016,'#94402E'),
(7,'Bibémiré AS','BIB','Bibémiré',2020,'#7A3350'),
(8,'Kolléré Sporting','KOL','Kolléré',2018,'#5E2A3C'),
(9,'Plateau FC','PLA','Plateau',2021,'#8A5230'),
(10,'Yelwa Athletic','YEL','Yelwa',2022,'#4A2030');

INSERT INTO competition_team (competition_id,team_id)
SELECT 1, id FROM teams UNION ALL SELECT 2, id FROM teams;

INSERT INTO players (team_id,jersey_number,first_name,last_name,position,position_label,birth_date,height_cm) VALUES
(1,1,'Adamou','Bouba','GB','Gardien','2006-02-11',185),
(1,4,'Hamidou','Djalo','DEF','Défenseur central','2005-05-02',181),
(1,5,'Bachirou','Nana','DEF','Latéral droit','2007-01-19',175),
(1,8,'Moussa','Sanda','MIL','Milieu relayeur','2006-09-30',177),
(1,9,'Ibrahim','Ousmanou','ATT','Avant-centre','2007-03-14',178),
(1,11,'Salifou','Djibril','ATT','Ailier gauche','2008-06-22',172),
(2,10,'Aboubakar','Hamadou','MIL','Meneur de jeu','2007-04-08',176),
(2,7,'Aboubakar','Sali','ATT','Ailier droit','2006-11-02',174),
(3,9,'Mahamat','Yaya','ATT','Avant-centre','2006-07-27',180),
(5,9,'Oumarou','Bello','ATT','Avant-centre','2007-02-05',179);

INSERT INTO users (name,email,password_hash,role) VALUES
('Administrateur GFC','admin@gfc.cm', '$2y$10$e0NRl3G8ZbYw8kq0PqZC9uV0kcQ0kR7iH0wq2VZaJHkq0S5v7bO2u','admin');
-- mot de passe du compte de démonstration : gfc2026  (à régénérer avec password_hash())
