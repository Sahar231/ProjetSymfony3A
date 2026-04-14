package edu.connexion3a36.services;

import edu.connexion3a36.entities.Resultat;
import edu.connexion3a36.tools.MyConnection;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class ResultatService {

    private final Connection cnx;

    public ResultatService() {
        this.cnx = MyConnection.getInstance().getCnx();
    }

    public void ajouterResultat(Resultat r) throws SQLException {
        String sql = "INSERT INTO resultat (score, created_at) VALUES (?, ?)";
        try (PreparedStatement pst = cnx.prepareStatement(sql)) {
            pst.setDouble(1, r.getScore());
            if (r.getCreatedAt() != null) {
                pst.setTimestamp(2, Timestamp.valueOf(r.getCreatedAt()));
            } else {
                pst.setTimestamp(2, null);
            }
            pst.executeUpdate();
        }
    }

    public List<Resultat> afficherResultat() throws SQLException {
        String sql = "SELECT id_resultat, score, created_at FROM resultat";
        List<Resultat> list = new ArrayList<>();
        try (PreparedStatement pst = cnx.prepareStatement(sql);
             ResultSet rs = pst.executeQuery()) {
            while (rs.next()) {
                Resultat res = new Resultat();
                res.setIdResultat(rs.getInt("id_resultat"));
                res.setScore(rs.getDouble("score"));
                Timestamp ts = rs.getTimestamp("created_at");
                if (ts != null) res.setCreatedAt(ts.toLocalDateTime());
                list.add(res);
            }
        }
        return list;
    }

    public void modifierResultat(Resultat r) throws SQLException {
        String sql = "UPDATE resultat SET score = ?, created_at = ? WHERE id_resultat = ?";
        try (PreparedStatement pst = cnx.prepareStatement(sql)) {
            pst.setDouble(1, r.getScore());
            if (r.getCreatedAt() != null) {
                pst.setTimestamp(2, Timestamp.valueOf(r.getCreatedAt()));
            } else {
                pst.setTimestamp(2, null);
            }
            pst.setInt(3, r.getIdResultat());
            pst.executeUpdate();
        }
    }

    public void supprimerResultat(int id) throws SQLException {
        String sql = "DELETE FROM resultat WHERE id_resultat = ?";
        try (PreparedStatement pst = cnx.prepareStatement(sql)) {
            pst.setInt(1, id);
            pst.executeUpdate();
        }
    }
}
