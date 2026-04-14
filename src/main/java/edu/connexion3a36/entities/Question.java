package edu.connexion3a36.entities;

import java.util.Objects;

public class Question {

    private int idQuestion;
    private String question;
    private String correctAnswer;
    private double score;
    private String type;

    public Question() {}

    public Question(String question, String correctAnswer, double score, String type) {
        this.question = question;
        this.correctAnswer = correctAnswer;
        this.score = score;
        this.type = type;
    }

    public Question(int idQuestion, String question, String correctAnswer, double score, String type) {
        this.idQuestion = idQuestion;
        this.question = question;
        this.correctAnswer = correctAnswer;
        this.score = score;
        this.type = type;
    }

    public int getIdQuestion() { return idQuestion; }
    public void setIdQuestion(int idQuestion) { this.idQuestion = idQuestion; }

    public String getQuestion() { return question; }
    public void setQuestion(String question) { this.question = question; }

    public String getCorrectAnswer() { return correctAnswer; }
    public void setCorrectAnswer(String correctAnswer) { this.correctAnswer = correctAnswer; }

    public double getScore() { return score; }
    public void setScore(double score) { this.score = score; }

    public String getType() { return type; }
    public void setType(String type) { this.type = type; }

    @Override
    public String toString() {
        return "Question{" +
                "idQuestion=" + idQuestion +
                ", question='" + question + '\'' +
                ", correctAnswer='" + correctAnswer + '\'' +
                ", score=" + score +
                ", type='" + type + '\'' +
                '}';
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;
        Question question1 = (Question) o;
        return idQuestion == question1.idQuestion;
    }

    @Override
    public int hashCode() {
        return Objects.hash(idQuestion);
    }
}

