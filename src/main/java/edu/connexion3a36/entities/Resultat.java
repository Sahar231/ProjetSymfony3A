package edu.connexion3a36.entities;

import java.time.LocalDateTime;
import java.util.Objects;

public class Resultat {
    private int idResultat;
    private double score;
    private LocalDateTime createdAt;

    public Resultat() {}

    public Resultat(double score, LocalDateTime createdAt) {
        this.score = score;
        this.createdAt = createdAt;
    }

    public Resultat(int idResultat, double score, LocalDateTime createdAt) {
        this.idResultat = idResultat;
        this.score = score;
        this.createdAt = createdAt;
    }

    public int getIdResultat() { return idResultat; }
    public void setIdResultat(int idResultat) { this.idResultat = idResultat; }

    public double getScore() { return score; }
    public void setScore(double score) { this.score = score; }

    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    @Override
    public String toString() {
        return "Resultat{" +
                "idResultat=" + idResultat +
                ", score=" + score +
                ", createdAt=" + createdAt +
                '}';
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Resultat resultat = (Resultat) o;
        return idResultat == resultat.idResultat;
    }

    @Override
    public int hashCode() {
        return Objects.hash(idResultat);
    }
}
